<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SshController extends AbstractController {

	#[Route( '/ssh/generate', name: 'app_ssh_generate' )]
	public function generate(): Response {
		return $this->render(
			'ssh/generate.html.twig',
			array(
				'controller_name' => 'SshController',
				'title'           => 'SSH Key Generator',
			)
		);
	}

	#[Route( '/ssh/rsync', name: 'app_ssh_rsync' )]
	public function rsync(): Response {
		return $this->render(
			'ssh/rsync.html.twig',
			array(
				'controller_name' => 'SshController',
				'title'           => 'Rsync Command Generator',
			)
		);
	}

	#[Route( '/ssh/tunnel', name: 'app_ssh_tunnel' )]
	public function tunnel(): Response {
		return $this->render(
			'ssh/tunnel.html.twig',
			array(
				'controller_name' => 'SshController',
				'title'           => 'SSH Tunnel Generator',
			)
		);
	}

	#[Route( '/api/ssh/generate', name: 'api_ssh_generate', methods: array( 'POST' ) )]
	public function apiGenerate( Request $request ): JsonResponse {
		try {
			$body = json_decode( $request->getContent(), true );

			$key_type = $body['keyType'] ?? 'rsa';
			$key_size = (int) ( $body['keySize'] ?? 2048 );
			$password = $body['password'] ?? '';
			$comment  = $body['comment'] ?? 'generated-key';

			// Handle ed25519 separately using sodium
			if ( $key_type === 'ed25519' ) {
				return $this->generateEd25519( $password, $comment );
			}

			// Validate key size based on type
			if ( $key_type === 'rsa' && ! in_array( $key_size, array( 2048, 4096 ) ) ) {
				$key_size = 2048;
			} elseif ( $key_type === 'dsa' ) {
				$key_size = 1024; // DSA only supports 1024 bits
			} elseif ( $key_type === 'ec' && ! in_array( $key_size, array( 256, 384, 521 ) ) ) {
				$key_size = 256;
			}

			// Configure key generation
			$config = array(
				'private_key_bits' => $key_size,
			);

			if ( $key_type === 'rsa' ) {
				$config['private_key_type'] = OPENSSL_KEYTYPE_RSA;
			} elseif ( $key_type === 'dsa' ) {
				$config['private_key_type'] = OPENSSL_KEYTYPE_DSA;
			} elseif ( $key_type === 'ec' ) {
				$config['private_key_type'] = OPENSSL_KEYTYPE_EC;
				$config['curve_name']       = $this->getEcCurve( $key_size );
			}

			// Generate private key
			$private_key_resource = openssl_pkey_new( $config );

			if ( ! $private_key_resource ) {
				return new JsonResponse(
					array(
						'error'   => 'Key generation failed',
						'message' => openssl_error_string(),
					),
					400
				);
			}

			// Export private key
			$passphrase = empty( $password ) ? null : $password;
			openssl_pkey_export( $private_key_resource, $private_key, $passphrase );

			// Get public key
			$public_key_details = openssl_pkey_get_details( $private_key_resource );
			$public_key         = $public_key_details['key'];

			// Convert to SSH format
			$ssh_public_key = $this->convertToSshFormat( $public_key, $key_type, $comment );

			return new JsonResponse(
				array(
					'success'    => true,
					'privateKey' => $private_key,
					'publicKey'  => $ssh_public_key,
					'keyType'    => strtoupper( $key_type ),
					'keySize'    => $key_size,
				),
				200
			);

		} catch ( \Throwable $e ) {
			return new JsonResponse(
				array(
					'error'   => 'Key generation failed',
					'message' => $e->getMessage(),
				),
				400
			);
		}
	}

	private function generateEd25519( string $password, string $comment ): JsonResponse {
		try {
			// Generate Ed25519 keypair using sodium
			$keypair = sodium_crypto_sign_keypair();
			$private = sodium_crypto_sign_secretkey( $keypair );
			$public  = sodium_crypto_sign_publickey( $keypair );

			// Convert to OpenSSH format
			$private_key = $this->encodeEd25519PrivateKey( $private, $password );
			$public_key  = 'ssh-ed25519 ' . base64_encode( "\x00\x00\x00\x0bssh-ed25519" . pack( 'N', 32 ) . $public ) . ' ' . $comment;

			return new JsonResponse(
				array(
					'success'    => true,
					'privateKey' => $private_key,
					'publicKey'  => $public_key,
					'keyType'    => 'ED25519',
					'keySize'    => 256,
				),
				200
			);
		} catch ( \Throwable $e ) {
			return new JsonResponse(
				array(
					'error'   => 'Ed25519 key generation failed',
					'message' => $e->getMessage(),
				),
				400
			);
		}
	}

	private function encodeEd25519PrivateKey( string $private_key_data, string $password ): string {
		// OpenSSH private key format for Ed25519
		$key_data = "-----BEGIN OPENSSH PRIVATE KEY-----\n";

		if ( ! empty( $password ) ) {
			$key_data .= "(Password-protected Ed25519 keys require ssh-keygen)\n";
			$key_data .= 'Base64 raw key: ' . base64_encode( $private_key_data ) . "\n";
		} else {
			// Simplified OpenSSH format (unencrypted)
			$magic      = "openssh-key-v1\x00";
			$cipher     = 'none';
			$kdf        = 'none';
			$public_key = substr( $private_key_data, 32 ); // Last 32 bytes
			$check_int  = random_int( 0, 0xFFFFFFFF );

			// Build the unencrypted private key section
			$private_section  = pack( 'NN', $check_int, $check_int );
			$private_section .= "\x00\x00\x00\x0bssh-ed25519"; // keytype
			$private_section .= pack( 'N', 32 ) . $public_key; // public key
			$private_section .= pack( 'N', 64 ) . $private_key_data; // private key (64 bytes for ed25519)
			$private_section .= "\x00\x00\x00\x00"; // comment (empty)

			// Pad to block size
			$block_size = 8;
			$pad_len    = $block_size - ( strlen( $private_section ) % $block_size );
			for ( $i = 1; $i <= $pad_len; $i++ ) {
				$private_section .= chr( $i );
			}

			$blob  = $magic;
			$blob .= pack( 'N', strlen( $cipher ) ) . $cipher;
			$blob .= pack( 'N', strlen( $kdf ) ) . $kdf;
			$blob .= pack( 'N', 0 ); // kdf options length
			$blob .= pack( 'N', 1 ); // number of keys

			// Public key section
			$public_section  = "\x00\x00\x00\x0bssh-ed25519";
			$public_section .= pack( 'N', 32 ) . $public_key;
			$blob           .= pack( 'N', strlen( $public_section ) ) . $public_section;

			// Private key section
			$blob .= pack( 'N', strlen( $private_section ) ) . $private_section;

			$encoded   = base64_encode( $blob );
			$key_data .= chunk_split( $encoded, 70, "\n" );
		}

		$key_data .= "-----END OPENSSH PRIVATE KEY-----\n";
		return $key_data;
	}

	private function getEcCurve( int $key_size ): string {
		switch ( $key_size ) {
			case 256:
				return 'prime256v1';
			case 384:
				return 'secp384r1';
			case 521:
				return 'secp521r1';
			default:
				return 'prime256v1';
		}
	}

	private function convertToSshFormat( string $public_key, string $key_type, string $comment ): string {
		// Extract the public key from PEM format
		$public_key = str_replace( array( '-----BEGIN PUBLIC KEY-----', '-----END PUBLIC KEY-----', "\n", "\r" ), '', $public_key );
		$decoded    = base64_decode( $public_key );

		// Parse the DER format
		$key_type_map = array(
			'rsa' => 'ssh-rsa',
			'dsa' => 'ssh-dss',
			'ec'  => 'ecdsa-sha2-nistp256',
		);

		$ssh_type = $key_type_map[ $key_type ] ?? 'ssh-rsa';

		// For simplicity, we'll format it as OpenSSH format
		// Note: Full implementation would require proper DER parsing
		// This is a simplified version
		return $ssh_type . ' ' . $public_key . ' ' . $comment;
	}
}
