<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApacheController extends AbstractController {

	#[Route( '/apache/vhost', name: 'app_apache_vhost' )]
	public function appVhost(): Response {
		return $this->render(
			'apache/vhost.html.twig',
			array(
				'controller_name' => 'ApacheController',
				'title'           => 'VirtualHost',
			)
		);
	}

	#[Route( '/apache/redirections', name: 'app_apache_redirections' )]
	public function appRedirections(): Response {
		return $this->render(
			'apache/redirections.html.twig',
			array(
				'controller_name' => 'ApacheController',
				'title'           => 'Redirections',
			)
		);
	}

	#[Route( '/apache/basicauth', name: 'app_apache_basicauth' )]
	public function appBasicAuth(): Response {
		return $this->render(
			'apache/basic_auth.html.twig',
			array(
				'controller_name' => 'ApacheController',
				'title'           => 'BasicAuth',
			)
		);
	}

	#[Route( '/api/apache/vhost', name: 'api_apache_vhost', methods: array( 'POST' ) )]
	public function apiApacheVhost( Request $request ): JsonResponse {
		try {
			// Get data from the request body
			$body = $request->getContent();
			if ( empty( $body ) ) {
				return new JsonResponse( array( 'error' => 'No content provided' ), 400 );
			}
			$body = json_decode( $body, true );

			// dots to underscores
			$underscoreName = str_replace( '.', '_', $body['serverName'] ?? '' );

			$vars = array(
				'logName'      => $underscoreName,
				'serverName'   => $body['serverName'] ?? '',
				'serverAlias'  => $body['serverAlias'] ?? '',
				'documentRoot' => $body['documentRoot'] ?? '',
				'directory'    => $body['directory'] ?? '',
				'httpPort'     => $body['httpPort'] ?? '80',
				'httpsPort'    => $body['httpsPort'] ?? '443',
				'bindIP'       => $body['bindIP'] ?? '*',
				'preferences'  => array(
					'http'        => in_array( 'http', $body['preferences'] ?? array() ),
					'https'       => in_array( 'https', $body['preferences'] ?? array() ),
					'serverAlias' => in_array( 'serverAlias', $body['preferences'] ?? array() ),
				),
			);

			$str = $this->renderView( 'apache/vhost.conf.twig', $vars );

			return new JsonResponse(
				array(
					'success'   => true,
					'formatted' => $str,
					'filename'  => $underscoreName . '.conf',
				),
				200
			);

		} catch ( \Throwable $e ) {
			return new JsonResponse(
				array(
					'error'   => 'Invalid input',
					'message' => $e->getMessage(),
				),
				400
			);
		}
	}

	#[Route( '/api/apache/redirections', name: 'api_apache_redirections', methods: array( 'POST' ) )]
	public function apiApacheRedirections( Request $request ): JsonResponse {
		try {
			// Get data from the request body
			$content = $request->getContent();

			if ( empty( $content ) ) {
				return new JsonResponse( array( 'error' => 'No content provided' ), 400 );
			}

			$lines = explode( "\n", $content );
			$arr   = array();

			foreach ( $lines as $line ) {
				$ex = explode( ' ', $line );

				if ( count( $ex ) === 4 ) {
					// Redirect 301 /_SHOP/files/products/5010010001561.jpg?id=8169535 /
					$from   = parse_url( $ex[2] );
					$to     = parse_url( $ex[3] );
					$status = $ex[1];

					// check if from path has trailing slash
					if ( substr( $from['path'], -1 ) === '/' ) {
						$from['path']  = substr( $from['path'], 0, -1 );
						$from['path'] .= '/?';
					}

					// remove leading slash from path
					if ( substr( $from['path'], -1 ) === '/' ) {
						$from['path'] = substr( $from['path'], 0, -1 );
					}

					if ( ! empty( $from['query'] ) ) {
						$arr[] = 'RewriteCond %{QUERY_STRING} ^' . $from['query'] . '$';
						$arr[] = 'RewriteRule ^' . $from['path'] . '$ ' . rtrim( $to['path'], '_' ) . '? [R=' . $status . ',L]';
						$arr[] = '';
					} else {
						$arr[] = 'RewriteRule ^' . $from['path'] . '$ ' . rtrim( $to['path'], '_' ) . '? [R=' . $status . ',L]';
						$arr[] = '';
					}
				} elseif ( count( $ex ) === 3 ) {
					// /_SHOP/files/products/5010010001561.jpg?id=8169535 / 301
					$from   = parse_url( $ex[0] );
					$to     = parse_url( $ex[1] );
					$status = $ex[2];

					// check if from path has trailing slash
					if ( substr( $from['path'], -1 ) === '/' ) {
						$from['path']  = substr( $from['path'], 0, -1 );
						$from['path'] .= '/?';
					}

					// remove leading slash from path
					if ( substr( $from['path'], -1 ) === '/' ) {
						$from['path'] = substr( $from['path'], 0, -1 );
					}

					if ( ! empty( $from['query'] ) ) {
						$arr[] = 'RewriteCond %{QUERY_STRING} ^' . $from['query'] . '$';
						$arr[] = 'RewriteRule ^' . $from['path'] . '$ ' . rtrim( $to['path'], '_' ) . '? [R=' . $status . ',L]';
						$arr[] = '';
					} else {
						$arr[] = 'RewriteRule ^' . $from['path'] . '$ ' . rtrim( $to['path'], '_' ) . '? [R=' . $status . ',L]';
						$arr[] = '';
					}
				} elseif ( count( $ex ) === 2 ) {
					// /_SHOP/files/products/5010010001561.jpg?id=8169535 /
					$from   = parse_url( $ex[0] );
					$to     = parse_url( $ex[1] );
					$status = '301';

					// check if from path has trailing slash
					if ( substr( $from['path'], -1 ) === '/' ) {
						$from['path']  = substr( $from['path'], 0, -1 );
						$from['path'] .= '/?';
					}

					// remove leading slash from path
					if ( substr( $from['path'], -1 ) === '/' ) {
						$from['path'] = substr( $from['path'], 0, -1 );
					}

					if ( ! empty( $from['query'] ) ) {
						$arr[] = 'RewriteCond %{QUERY_STRING} ^' . $from['query'] . '$';
						$arr[] = 'RewriteRule ^' . $from['path'] . '$ ' . rtrim( $to['path'], '_' ) . '? [R=' . $status . ',L]';
						$arr[] = '';
					} else {
						$arr[] = 'RewriteRule ^' . $from['path'] . '$ ' . rtrim( $to['path'], '_' ) . '? [R=' . $status . ',L]';
						$arr[] = '';
					}
				} else {
					$arr[] = 'Unknown format for line: ' . $line;
				}
			}

			$str = implode( "\n", $arr );

			return new JsonResponse(
				array(
					'success'   => true,
					'formatted' => $str,
				),
				200
			);

		} catch ( \Throwable $e ) {
			return new JsonResponse(
				array(
					'error'   => 'Invalid input',
					'message' => $e->getMessage(),
				),
				400
			);
		}
	}

	#[Route( '/api/apache/basicauth', name: 'api_apache_basicauth', methods: array( 'POST' ) )]
	public function apiApacheBasicAuth( Request $request ): JsonResponse {
		try {
			// Get data from the request body
			$body = $request->getContent();
			if ( empty( $body ) ) {
				return new JsonResponse( array( 'error' => 'No content provided' ), 400 );
			}
			$body = json_decode( $body, true );

			$htpasswdPath   = $body['htpasswdPath'] ?? '/var/www/html/.htpasswd';
			$username       = $body['username'] ?? 'admin';
			$password       = $body['password'] ?? '';
			$authName       = $body['authName'] ?? 'Authorization required';
			$allowImages    = isset( $body['allowImages'] ) && $body['allowImages'] === '1';
			$allowLocalhost = isset( $body['allowLocalhost'] ) && $body['allowLocalhost'] === '1';
			$allowCrawlers  = isset( $body['allowCrawlers'] ) && $body['allowCrawlers'] === '1';

			if ( empty( $password ) ) {
				return new JsonResponse( array( 'error' => 'Password is required' ), 400 );
			}

			// Generate APR1-MD5 password hash
			$hashedPassword = $this->generateApr1Hash( $password );

			// Generate .htaccess content
			$htaccessContent = $this->renderView(
				'apache/basic_auth.conf.twig',
				array(
					'htpasswdPath'   => $htpasswdPath,
					'username'       => $username,
					'authName'       => $authName,
					'allowImages'    => $allowImages,
					'allowLocalhost' => $allowLocalhost,
					'allowCrawlers'  => $allowCrawlers,
				)
			);

			// Generate .htpasswd content
			$htpasswdContent = $username . ':' . $hashedPassword;

			return new JsonResponse(
				array(
					'success'  => true,
					'htaccess' => $htaccessContent,
					'htpasswd' => $htpasswdContent,
				),
				200
			);

		} catch ( \Throwable $e ) {
			return new JsonResponse(
				array(
					'error'   => 'Invalid input',
					'message' => $e->getMessage(),
				),
				400
			);
		}
	}

	/**
	 * Generate APR1-MD5 password hash (Apache htpasswd compatible)
	 */
	private function generateApr1Hash( string $plainPassword ): string {
		$salt = substr( str_shuffle( 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789' ), 0, 8 );
		$len  = strlen( $plainPassword );
		$text = $plainPassword . '$apr1$' . $salt;
		$bin  = pack( 'H32', md5( $plainPassword . $salt . $plainPassword ) );

		for ( $i = $len; $i > 0; $i -= 16 ) {
			$text .= substr( $bin, 0, min( 16, $i ) );
		}

		for ( $i = $len; $i > 0; $i >>= 1 ) {
			$text .= ( $i & 1 ) ? chr( 0 ) : $plainPassword[0];
		}

		$bin = pack( 'H32', md5( $text ) );

		for ( $i = 0; $i < 1000; $i++ ) {
			$new = ( $i & 1 ) ? $plainPassword : $bin;
			if ( $i % 3 ) {
				$new .= $salt;
			}
			if ( $i % 7 ) {
				$new .= $plainPassword;
			}
			$new .= ( $i & 1 ) ? $bin : $plainPassword;
			$bin  = pack( 'H32', md5( $new ) );
		}

		$tmp = '';
		for ( $i = 0; $i < 5; $i++ ) {
			$k = $i + 6;
			$j = $i + 12;
			if ( $j == 16 ) {
				$j = 5;
			}
			$tmp = $bin[ $i ] . $bin[ $k ] . $bin[ $j ] . $tmp;
		}
		$tmp = chr( 0 ) . chr( 0 ) . $bin[11] . $tmp;
		$tmp = strtr(
			strrev( substr( base64_encode( $tmp ), 2 ) ),
			'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/',
			'./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'
		);

		return '$apr1$' . $salt . '$' . $tmp;
	}
}
