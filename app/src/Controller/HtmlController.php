<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HtmlController extends AbstractController {

	#[Route( '/html/validator', name: 'app_html_validator' )]
	public function validator(): Response {
		return $this->render(
			'html/validator.html.twig',
			array(
				'controller_name' => 'HtmlController',
				'title'           => 'HTML Validator',
			)
		);
	}

	#[Route( '/html/formatter', name: 'app_html_formatter' )]
	public function formatter(): Response {
		return $this->render(
			'html/formatter.html.twig',
			array(
				'controller_name' => 'HtmlController',
				'title'           => 'HTML Formatter',
			)
		);
	}

	#[Route( '/html/colors', name: 'app_html_colors' )]
	public function colors(): Response {
		return $this->render(
			'html/colors.html.twig',
			array(
				'controller_name' => 'HtmlController',
				'title'           => 'Color Converter',
			)
		);
	}

	#[Route( '/api/html/validate', name: 'api_html_validate', methods: array( 'POST' ) )]
	public function apiValidate( Request $request ): JsonResponse {
		try {
			$content = $request->getContent();

			if ( empty( $content ) ) {
				return new JsonResponse( array( 'error' => 'No content provided' ), 400 );
			}

			$errors   = array();
			$warnings = array();
			$isValid  = true;

			// Use libxml to capture errors
			libxml_use_internal_errors( true );
			libxml_clear_errors();

			// Try to load the HTML
			$doc = new \DOMDocument( '1.0', 'UTF-8' );
			$doc->loadHTML( $content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );

			// Get errors
			$libxmlErrors = libxml_get_errors();

			foreach ( $libxmlErrors as $error ) {
				$errorData = array(
					'line'    => $error->line,
					'column'  => $error->column,
					'message' => trim( $error->message ),
					'level'   => $error->level,
				);

				if ( $error->level === LIBXML_ERR_ERROR || $error->level === LIBXML_ERR_FATAL ) {
					$errors[] = $errorData;
					$isValid  = false;
				} else {
					$warnings[] = $errorData;
				}
			}

			libxml_clear_errors();
			libxml_use_internal_errors( false );

			return new JsonResponse(
				array(
					'success'      => true,
					'valid'        => $isValid,
					'errors'       => $errors,
					'warnings'     => $warnings,
					'errorCount'   => count( $errors ),
					'warningCount' => count( $warnings ),
				),
				200
			);

		} catch ( \Throwable $e ) {
			return new JsonResponse(
				array(
					'error'   => 'Validation failed',
					'message' => $e->getMessage(),
				),
				400
			);
		}
	}

	#[Route( '/api/html/format', name: 'api_html_format', methods: array( 'POST' ) )]
	public function apiFormat( Request $request ): JsonResponse {
		try {
			$body = json_decode( $request->getContent(), true );

			if ( empty( $body['content'] ) ) {
				return new JsonResponse( array( 'error' => 'No content provided' ), 400 );
			}

			$content = $body['content'];
			$mode    = $body['mode'] ?? 'beautify'; // beautify or minify

			if ( $mode === 'minify' ) {
				$formatted = $this->minifyHtml( $content );
			} else {
				$formatted = $this->beautifyHtml( $content );
			}

			return new JsonResponse(
				array(
					'success'   => true,
					'formatted' => $formatted,
					'mode'      => $mode,
				),
				200
			);

		} catch ( \Throwable $e ) {
			return new JsonResponse(
				array(
					'error'   => 'Formatting failed',
					'message' => $e->getMessage(),
				),
				400
			);
		}
	}

	private function beautifyHtml( string $html ): string {
		libxml_use_internal_errors( true );

		$doc                     = new \DOMDocument( '1.0', 'UTF-8' );
		$doc->preserveWhiteSpace = false;
		$doc->formatOutput       = true;

		// Load HTML
		$doc->loadHTML( $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );

		libxml_clear_errors();
		libxml_use_internal_errors( false );

		$output = $doc->saveHTML();

		// Clean up the output
		$output = preg_replace( '/^\s+/m', '', $output ); // Remove leading spaces
		$output = $this->indentHtml( $output );

		return trim( $output );
	}

	private function minifyHtml( string $html ): string {
		// Remove HTML comments
		$html = preg_replace( '/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/s', '', $html );

		// Remove whitespace between tags
		$html = preg_replace( '/>\s+</', '><', $html );

		// Remove multiple spaces
		$html = preg_replace( '/\s+/', ' ', $html );

		// Trim
		$html = trim( $html );

		return $html;
	}

	private function indentHtml( string $html ): string {
		$formatted = '';
		$indent    = 0;
		$lines     = explode( "\n", $html );

		foreach ( $lines as $line ) {
			$line = trim( $line );

			if ( empty( $line ) ) {
				continue;
			}

			// Decrease indent for closing tags
			if ( preg_match( '/^<\//', $line ) ) {
				$indent = max( 0, $indent - 1 );
			}

			// Add indented line
			$formatted .= str_repeat( '  ', $indent ) . $line . "\n";

			// Increase indent for opening tags (but not self-closing or single-line tags)
			if ( preg_match( '/^<(?!\/)(?!area|base|br|col|embed|hr|img|input|link|meta|param|source|track|wbr)([a-z0-9]+)/i', $line )
				&& ! preg_match( '/\/>$/', $line )
				&& ! preg_match( '/<.+<\/.+>/', $line ) ) {
				++$indent;
			}
		}

		return trim( $formatted );
	}
}
