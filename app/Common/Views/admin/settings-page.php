<?php
/**
 * This is the error page HTML.
 *
 * @since 4.1.9
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable Generic.Files.LineLength.MaxExceeded
$logoImage = 'data:image/svg+xml;base64,PHN2ZyB2aWV3Qm94PSIwIDAgMTMyIDI2IiBmaWxsPSJub25lIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIGNsYXNzPSJhaW9zZW8tbG9nbyI+Cgk8cGF0aAoJCWZpbGwtcnVsZT0iZXZlbm9kZCIKCQljbGlwLXJ1bGU9ImV2ZW5vZGQiCgkJZD0iTTExOS4wMzggMjUuOTI0MUMxMjYuMTk3IDI1LjkyNDEgMTMyIDIwLjEyMDggMTMyIDEyLjk2MkMxMzIgNS44MDMzIDEyNi4xOTcgMCAxMTkuMDM4IDBDMTExLjg3OSAwIDEwNi4wNzYgNS44MDMzIDEwNi4wNzYgMTIuOTYyQzEwNi4wNzYgMjAuMTIwOCAxMTEuODc5IDI1LjkyNDEgMTE5LjAzOCAyNS45MjQxWk0xMTYuOTc0IDQuNzQ0MDhDMTE2Ljc5OCA0LjQ3NjQ4IDExNi40NzMgNC4zNTEzNiAxMTYuMTc1IDQuNDU2NzJDMTE1LjgzNSA0LjU3NjczIDExNS41MDMgNC43MTc4NyAxMTUuMTggNC44NzkyOUMxMTQuODk3IDUuMDIwOTggMTE0Ljc1NSA1LjM0NDY2IDExNC44MTcgNS42NjAzM0wxMTUuMDM5IDYuNzg1MDdDMTE1LjA5NiA3LjA3NDU3IDExNC45NzggNy4zNjgzOSAxMTQuNzU0IDcuNTU1MDRDMTE0LjQgNy44NTAwMyAxMTQuMDcyIDguMTgzNTQgMTEzLjc3OSA4LjU1MjY4QzExMy41OTcgOC43ODIxMiAxMTMuMzA5IDguOTAzNzMgMTEzLjAyNSA4Ljg0NjM4TDExMS45MjMgOC42MjM2NEMxMTEuNjEzIDguNTYxMDcgMTExLjI5NiA4LjcwNzQ3IDExMS4xNTkgOC45OTczOEMxMTEuMDgxIDkuMTYxMTYgMTExLjAwNyA5LjMyODM3IDExMC45MzggOS40OTg5MkMxMTAuODY5IDkuNjY5NDcgMTEwLjgwNiA5Ljg0MDkzIDExMC43NDggMTAuMDEzMUMxMTAuNjQ2IDEwLjMxNzkgMTEwLjc3IDEwLjY0OTYgMTExLjAzMyAxMC44Mjc5TDExMS45NjkgMTEuNDYyNUMxMTIuMjEgMTEuNjI2IDExMi4zMyAxMS45MTkgMTEyLjMwMSAxMi4yMTI4QzExMi4yNTQgMTIuNjg1NiAxMTIuMjU2IDEzLjE1NzUgMTEyLjMwNCAxMy42MjE3QzExMi4zMzQgMTMuOTE1NCAxMTIuMjE1IDE0LjIwODggMTExLjk3NCAxNC4zNzMxTDExMS4wNCAxNS4wMTE0QzExMC43NzggMTUuMTkwNiAxMTAuNjU1IDE1LjUyMjQgMTEwLjc1OCAxNS44MjY4QzExMC44NzYgMTYuMTczNCAxMTEuMDE0IDE2LjUxMjUgMTExLjE3MiAxNi44NDE5QzExMS4zMTEgMTcuMTMxMSAxMTEuNjI5IDE3LjI3NjEgMTExLjkzOCAxNy4yMTI1TDExMy4wNCAxNi45ODU3QzExMy4zMjQgMTYuOTI3MyAxMTMuNjEyIDE3LjA0NzggMTEzLjc5NSAxNy4yNzY3QzExNC4wODQgMTcuNjM4NCAxMTQuNDExIDE3Ljk3MjMgMTE0Ljc3MiAxOC4yNzE1QzExNC45OTcgMTguNDU3NCAxMTUuMTE2IDE4Ljc1MDggMTE1LjA2IDE5LjA0MDVMMTE0Ljg0MiAyMC4xNjU2QzExNC43ODEgMjAuNDgxNyAxMTQuOTI0IDIwLjgwNDkgMTE1LjIwOCAyMC45NDU1QzExNS4zNjkgMjEuMDI0OSAxMTUuNTMzIDIxLjA5OTkgMTE1LjcgMjEuMTcwMkMxMTUuODY3IDIxLjI0MDUgMTE2LjAzNSAyMS4zMDUxIDExNi4yMDQgMjEuMzY0MkMxMTYuNjk3IDIxLjUzNjkgMTE3LjM4OCAyMC45MTg1IDExNy44OTkgMjAuNDYxM0MxMTguMTUxIDIwLjIzNTggMTE4LjMwNiAxOS45MTY3IDExOC4zMDggMTkuNTc1MUMxMTguMzA4IDE5LjU3MzIgMTE4LjMwOCAxOS41NzE0IDExOC4zMDggMTkuNTY5NkwxMTguMzA4IDE3LjY4ODJDMTE4LjMwOCAxNy42NjgyIDExOC4zMDkgMTcuNjQ4NSAxMTguMzEgMTcuNjI4OUMxMTYuODAxIDE3LjI2MDkgMTE1LjY4IDE1Ljg3NTkgMTE1LjY4IDE0LjIyMzZWMTIuMjI1OEMxMTUuNjggMTIuMDczOSAxMTUuOCAxMS45NTA4IDExNS45NDkgMTEuOTUwOEgxMTYuODg0VjkuOTg1MjFDMTE2Ljg4NCA5LjcxMzgxIDExNy4wOTkgOS40OTM4MSAxMTcuMzY1IDkuNDkzODFDMTE3LjYzMSA5LjQ5MzgxIDExNy44NDcgOS43MTM4MSAxMTcuODQ3IDkuOTg1MjFWMTEuOTUwOEgxMjAuMzc1VjkuOTg1MjFDMTIwLjM3NSA5LjcxMzgxIDEyMC41OTEgOS40OTM4MSAxMjAuODU3IDkuNDkzODFDMTIxLjEyMyA5LjQ5MzgxIDEyMS4zMzggOS43MTM4MSAxMjEuMzM4IDkuOTg1MjFWMTEuOTUwOEgxMjIuMjczQzEyMi40MjIgMTEuOTUwOCAxMjIuNTQyIDEyLjA3MzkgMTIyLjU0MiAxMi4yMjU4VjE0LjIyMzZDMTIyLjU0MiAxNS45MjgxIDEyMS4zNDggMTcuMzQ4MiAxMTkuNzY4IDE3LjY2MDhDMTE5Ljc2OCAxNy42Njk5IDExOS43NjggMTcuNjc5IDExOS43NjggMTcuNjg4MkwxMTkuNzY4IDE5LjU2MTVDMTE5Ljc2OCAxOS45MDk3IDExOS45MjggMjAuMjM0NiAxMjAuMTg3IDIwLjQ2MDlDMTIwLjcwNyAyMC45MTQzIDEyMS40MSAyMS41MjczIDEyMS45MDEgMjEuMzUzOUMxMjIuMjQxIDIxLjIzMzkgMTIyLjU3MyAyMS4wOTI3IDEyMi44OTYgMjAuOTMxM0MxMjMuMTc5IDIwLjc4OTYgMTIzLjMyMSAyMC40NjU5IDEyMy4yNTkgMjAuMTUwM0wxMjMuMDM3IDE5LjAyNTVDMTIyLjk4IDE4LjczNiAxMjMuMDk4IDE4LjQ0MjIgMTIzLjMyMiAxOC4yNTU1QzEyMy42NzYgMTcuOTYwNiAxMjQuMDA0IDE3LjYyNzEgMTI0LjI5NyAxNy4yNTc5QzEyNC40NzkgMTcuMDI4NSAxMjQuNzY3IDE2LjkwNjkgMTI1LjA1IDE2Ljk2NDJMMTI2LjE1MyAxNy4xODdDMTI2LjQ2MyAxNy4yNDk1IDEyNi43OCAxNy4xMDMxIDEyNi45MTcgMTYuODEzMkMxMjYuOTk1IDE2LjY0OTQgMTI3LjA2OSAxNi40ODIyIDEyNy4xMzggMTYuMzExN0MxMjcuMjA2IDE2LjE0MTIgMTI3LjI3IDE1Ljk2OTcgMTI3LjMyOCAxNS43OTc1QzEyNy40MyAxNS40OTI3IDEyNy4zMDYgMTUuMTYxMSAxMjcuMDQzIDE0Ljk4MjhMMTI2LjEwNyAxNC4zNDgxQzEyNS44NjYgMTQuMTg0NiAxMjUuNzQ2IDEzLjg5MTYgMTI1Ljc3NSAxMy41OTc4QzEyNS44MjIgMTMuMTI1IDEyNS44MiAxMi42NTMxIDEyNS43NzIgMTIuMTg4OUMxMjUuNzQyIDExLjg5NTIgMTI1Ljg2MSAxMS42MDE4IDEyNi4xMDIgMTEuNDM3NUwxMjcuMDM2IDEwLjc5OTJDMTI3LjI5OCAxMC42MjAxIDEyNy40MjEgMTAuMjg4MiAxMjcuMzE4IDkuOTgzODVDMTI3LjIgOS42MzcyMSAxMjcuMDYyIDkuMjk4MTUgMTI2LjkwMyA4Ljk2ODc0QzEyNi43NjUgOC42Nzk1NyAxMjYuNDQ3IDguNTM0NSAxMjYuMTM4IDguNTk4MTRMMTI1LjAzNiA4LjgyNDk0QzEyNC43NTIgOC44ODMzMSAxMjQuNDY0IDguNzYyNzcgMTI0LjI4MSA4LjUzMzkxQzEyMy45OTIgOC4xNzIyMiAxMjMuNjY1IDcuODM4MzIgMTIzLjMwNCA3LjUzOTE0QzEyMy4wNzkgNy4zNTMxOSAxMjIuOTU5IDcuMDU5NzkgMTIzLjAxNiA2Ljc3MDA5TDEyMy4yMzQgNS42NDUwMUMxMjMuMjk1IDUuMzI4OTYgMTIzLjE1MiA1LjAwNTY3IDEyMi44NjggNC44NjUxQzEyMi43MDcgNC43ODU2OCAxMjIuNTQzIDQuNzEwNzIgMTIyLjM3NiA0LjY0MDQzQzEyMi4yMDkgNC41NzAxNCAxMjIuMDQxIDQuNTA1NTEgMTIxLjg3MiA0LjQ0NjQ2QzEyMS41NzQgNC4zNDE5NCAxMjEuMjQ5IDQuNDY4MTkgMTIxLjA3NCA0LjczNjU0TDEyMC40NTIgNS42OTE4M0MxMjAuMjkyIDUuOTM3ODEgMTIwLjAwNSA2LjA2MDMyIDExOS43MTcgNi4wMzA2QzExOS4yNTMgNS45ODI3OSAxMTguNzkxIDUuOTg0NzMgMTE4LjMzNiA2LjAzMzUzQzExOC4wNDggNi4wNjQ0MSAxMTcuNzYxIDUuOTQyOTIgMTE3LjYgNS42OTc1MUwxMTYuOTc0IDQuNzQ0MDhaIgoJCWZpbGw9IiMwMDVBRTAiCgkvPgoJPHBhdGgKCQlmaWxsLXJ1bGU9ImV2ZW5vZGQiCgkJY2xpcC1ydWxlPSJldmVub2RkIgoJCWQ9Ik0xMDUuNTEzIDEuMDUzMzdIODguMjk0MVYyNS4xMDY4SDEwNS42MTVDMTA0LjgyMSAyMy40NDcyIDEwNC4xODUgMjEuNjk3OCAxMDMuNzI2IDE5Ljg3NzhIOTQuNDk2OFYxNS41NTAzSDEwMi45ODlDMTAyLjkxMiAxNC43MDEyIDEwMi44NzIgMTMuODQxMiAxMDIuODcyIDEyLjk3MkMxMDIuODcyIDEyLjA2NTggMTAyLjkxNSAxMS4xNjk2IDEwMi45OTkgMTAuMjg1M0g5NC40OTY4VjYuMjgyMzdIMTAzLjY3MkMxMDQuMTE1IDQuNDYzNzUgMTA0LjczNSAyLjcxNDM1IDEwNS41MTMgMS4wNTMzN1pNNzUuMzY3OSAyNS41Mzk1QzcwLjQ5OTUgMjUuNTM5NSA2Ny4xMDk2IDI0LjAyNDkgNjQuNjkzNSAyMS43MTY5TDY3Ljk3NTEgMTcuMDY0OUM2OS43MDYxIDE4Ljc5NTkgNzIuMzc0NyAyMC4yMzg0IDc1LjY1NjQgMjAuMjM4NEM3Ny43ODQgMjAuMjM4NCA3OS4wODIzIDE5LjMzNjggNzkuMDgyMyAxOC4xODI5Qzc5LjA4MjMgMTYuODEyNSA3Ny41MzE2IDE2LjI3MTYgNzQuOTcxMiAxNS43MzA2TDc0Ljc2NzQgMTUuNjg5OUM3MC44MTcgMTQuOTAxNiA2NS40NTA4IDEzLjgzMDYgNjUuNDUwOCA4LjIyOTczQzY1LjQ1MDggNC4xOTA3NyA2OC44NzY3IDAuNjkyNzQ5IDc1LjA0MzMgMC42OTI3NDlDNzguOTAxOSAwLjY5Mjc0OSA4Mi4yNTU3IDEuODQ2NzQgODQuODE2MSA0LjA0NjUyTDgxLjQyNjMgOC40ODIxNkM3OS40MDY4IDYuODIzMyA3Ni43NzQzIDUuOTkzODggNzQuNjQ2NiA1Ljk5Mzg4QzcyLjU5MTEgNS45OTM4OCA3MS43OTc3IDYuODIzMyA3MS43OTc3IDcuODY5MTFDNzEuNzk3NyA5LjEzMTI4IDczLjI3NjMgOS41NjQwMiA3NS45NDQ5IDEwLjA2ODlDNzkuOTExNyAxMC44OTgzIDg1LjM5MzEgMTIuMDUyMyA4NS4zOTMxIDE3LjQ5NzdDODUuMzkzMSAyMi4zMyA4MS44MjMgMjUuNTM5NSA3NS4zNjc5IDI1LjUzOTVaIgoJCWZpbGw9IiMwMDVBRTAiCgkvPgoJPHBhdGgKCQlkPSJNMTguNjY0NiAyNS4xMTg2SDI1LjIyNTNMMTYuMzg0MiAxLjcxNzU5SDguODA2MDZMMCAyNS4xMTg2SDYuNTYwNjlMNy43NTM1NSAyMS41NzUxSDE3LjQ3MThMMTguNjY0NiAyNS4xMTg2Wk0xMi41OTUxIDYuOTgwMThMMTUuODkzIDE2LjQ4NzlIOS4zMzIzMkwxMi41OTUxIDYuOTgwMThaIgoJCWZpbGw9IiMxNDFCMzgiCgkvPgoJPHBhdGgKCQlkPSJNMjcuOTk5IDI1LjExODZIMzQuMDMzNVYxLjcxNzU5SDI3Ljk5OVYyNS4xMTg2WiIKCQlmaWxsPSIjMTQxQjM4IgoJLz4KCTxwYXRoCgkJZD0iTTM3LjA1MDQgMTMuNDM1NkMzNy4wNTA0IDIwLjU1NzcgNDIuNDE4MyAyNS41Mzk2IDQ5LjU3NTQgMjUuNTM5NkM1Ni43MzI1IDI1LjUzOTYgNjIuMDY1MyAyMC41NTc3IDYyLjA2NTMgMTMuNDM1NkM2Mi4wNjUzIDYuMzEzNTggNTYuNzMyNSAxLjMzMTY3IDQ5LjU3NTQgMS4zMzE2N0M0Mi40MTgzIDEuMzMxNjcgMzcuMDUwNCA2LjMxMzU4IDM3LjA1MDQgMTMuNDM1NlpNNTUuOTI1NiAxMy40MzU2QzU1LjkyNTYgMTcuMjI0NyA1My40MzQ2IDIwLjIwNjggNDkuNTc1NCAyMC4yMDY4QzQ1LjY4MTEgMjAuMjA2OCA0My4xOTAxIDE3LjIyNDcgNDMuMTkwMSAxMy40MzU2QzQzLjE5MDEgOS42MTE0NyA0NS42ODExIDYuNjY0NDIgNDkuNTc1NCA2LjY2NDQyQzUzLjQzNDYgNi42NjQ0MiA1NS45MjU2IDkuNjExNDcgNTUuOTI1NiAxMy40MzU2WiIKCQlmaWxsPSIjMTQxQjM4IgoJLz4KPC9zdmc+';
$medium    = false !== strpos( AIOSEO_PHP_VERSION_DIR, 'pro' ) ? 'proplugin' : 'liteplugin';
?>
<style type="text/css">
	#aioseo-settings-area {
		visibility: hidden;
		margin: auto;
		width: 750px;
		max-width: 100%;
		animation: loadAioseoSettingsNoJSView 0s 5s forwards;
	}

	#aioseo-settings-error-loading-area {
		text-align: center;
		background-color: #fff;
		border: 1px solid #D6E2EC;
		padding: 15px 50px 30px;
		color: #141B38;
		margin: 82px 0;
	}

	#aioseo-settings-logo {
		max-width: 100%;
		width: 240px;
		padding: 30px 0 15px;
	}

	.aioseo-settings-button,
	.aioseo-settings-button:focus {
		margin-left: auto;
		background-color: #005ae0;
		border-color: #3380BC;
		border-bottom-width: 2px;
		color: #fff;
		border-radius: 3px;
		font-weight: 600;
		transition: all 0.1s ease-in-out;
		transition-duration: 0.2s;
		padding: 14px 35px;
		font-size: 16px;
		margin-top: 10px;
		margin-bottom: 20px;
		text-decoration: none;
		display: inline-block;
	}

	.aioseo-settings-button:hover {
		color: #fff;
		background-color: #1a82ea;
	}

	#aioseo-alert-message {
		position: relative;
		border-radius: 3px;
		padding: 12px 20px;
		font-size: 14px;
		color: #141B38;
		line-height: 1.4;
		border: 1px solid #DF2A4A;
		background-color: #FBE9EC;
	}

	#aioseo-settings-area h3 {
		font-size: 20px;
		color: #434343;
		font-weight: 500;
		line-height:1.4;
	}

	#aioseo-settings-area p {
		line-height: 1.5;
		margin: 1em 0;
		font-size: 16px;
		color: #434343;
		padding: 5px 20px 20px;
	}

	@keyframes loadAioseoSettingsNoJSView{
		to { visibility: visible; }
	}
</style>
<!--[if IE]>
	<style>
		#aioseo-settings-area{
			visibility: visible !important;
		}
	</style>
<![endif]-->

<script type="text/javascript">
	var ua   = window.navigator.userAgent;
	var msie = ua.indexOf( 'MSIE ' );
	if (0 < msie) {
		document.addEventListener('DOMContentLoaded', () => {
			var browserError = document.getElementById( 'aioseo-error-browser' ),
				jsError      = document.getElementById( 'aioseo-error-js' );

			jsError.style.display      = 'none';
			browserError.style.display = 'block';
		})
	} else {
		window.onerror = function myErrorHandler( errorMsg, url, lineNumber ) {
			/* Don't try to put error in container that no longer exists post-vue loading */
			var messageContainer = document.getElementById( 'aioseo-nojs-error-message' );
			if ( ! messageContainer ) {
				return false;
			}
			var message                    = document.getElementById( 'aioseo-alert-message' );
			message.innerHTML              = errorMsg;
			messageContainer.style.display = 'block';
			return false;
		}
	}
</script>

<div id="aioseo-settings-area">
	<div id="aioseo-settings-error-loading-area">
		<img id="aioseo-settings-logo" src="<?php echo esc_attr( $logoImage ); ?>" alt="<?php echo esc_attr( AIOSEO_PLUGIN_NAME ); ?>">

		<div id="aioseo-error-js">
			<h3><?php esc_html_e( 'Ooops! It Appears JavaScript Didn’t Load', 'all-in-one-seo-pack' ); ?></h3>

			<p>
				<?php
				printf(
					// Translators: 1 - Line break HTML tag, 2 - "AIOSEO".
					esc_html__( 'There seems to be an issue running JavaScript on your website. %1$s%2$s is built with JavaScript to give you the best experience possible.', 'all-in-one-seo-pack' ),
					'<br>',
					esc_attr( AIOSEO_PLUGIN_SHORT_NAME )
				);
				?>
			</p>

			<div style="display: none;" id="aioseo-nojs-error-message">
				<div id="aioseo-alert-message"></div>

				<p style="margin-top: 5px; font-size: 14px; color: #141B38;">
					<?php
					printf(
						// Translators: 1 - "AIOSEO".
						esc_html__( 'Copy the error message above and paste it in a message to the %1$s support team.', 'all-in-one-seo-pack' ),
						esc_attr( AIOSEO_PLUGIN_SHORT_NAME )
					);
					?>
				</p>
			</div>

			<a href="https://aioseo.com/docs/how-to-fix-javascript-errors/?utm_source=WordPress&utm_medium=<?php echo esc_attr( $medium ); ?>&utm_campaign=javascript-errors" class="aioseo-settings-button" target="_blank">
				<?php esc_html_e( 'Resolve This Issue', 'all-in-one-seo-pack' ); ?>
			</a>
		</div>

		<div id="aioseo-error-browser" style="display: none">
			<h3><?php esc_html_e( 'Your browser version is not supported', 'all-in-one-seo-pack' ); ?></h3>

			<p>
				<?php
				printf(
					// Translators: 1 - "AIOSEO".
					esc_html__( 'You are using a browser which is no longer supported by %1$s. Please update or use another browser in order to access the plugin settings.', 'all-in-one-seo-pack' ),
					esc_attr( AIOSEO_PLUGIN_SHORT_NAME )
				);
				?>
			</p>

			<a href="https://www.aioseo.com/docs/browser-support-policy/?utm_source=WordPress&utm_medium=<?php echo esc_attr( $medium ); ?>&utm_campaign=javascript-errors" class="aioseo-settings-button" target="_blank">
				<?php esc_html_e( 'View supported browsers', 'all-in-one-seo-pack' ); ?>
			</a>
		</div>
	</div>
</div>