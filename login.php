<?php
include_once("include/conn.php");
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Whatsapp CRM | Login</title>
	<link href="https://fonts.googleapis.com/css?family=Poppins:600&display=swap" rel="stylesheet" />
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
	<link rel="shortcut icon" type="image/x-icon" href="assets/images/ico/favicon.ico" />
	<style>
		body,
		html {
			margin: 0;
			padding: 0;
			height: 100%;
			font-family: 'Poppins', sans-serif;
			background-color: #f0f2f5;
			display: flex;
			flex-direction: column;
		}

		.container {
			flex: 1;
			display: flex;
			justify-content: center;
			align-items: center;
		}

		.form-box {
			width: 100%;
			max-width: 400px;
			background: white;
			padding: 40px;
			border-radius: 10px;
			box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
		}

		.form-box img {
			height: 45px;
			display: block;
			margin: 0 auto 20px;
		}

		.input-div {
			position: relative;
			margin-bottom: 20px;
		}

		.input-div i {
			position: absolute;
			left: 15px;
			top: 12px;
			color: #999;
		}

		.input-div input {
			width: 100%;
			padding: 12px 15px 12px 45px;
			border: none;
			border-radius: 30px;
			background: #f0f2f5;
			font-size: 14px;
			outline: none;
			box-sizing: border-box;
		}

		.input-div input:focus {
			background: #e4e6eb;
		}

		.btn {
			width: 100%;
			padding: 12px;
			background-color: #6c63ff;
			color: white;
			border: none;
			border-radius: 30px;
			font-size: 16px;
			cursor: pointer;
			margin-top: 10px;
		}

		.btn:hover {
			background-color: #5b54e4;
		}

		.links {
			text-align: center;
			margin-top: 15px;
			font-size: 12px;
		}

		.links a {
			color: <?= $bgColor; ?>;
			text-decoration: none;
			margin: 0 10px;
		}

		.links a:hover {
			text-decoration: none;
		}

		#login-error {
			color: #fff;
			text-align: center;
			margin-top: 15px;
			background: #d07474;
			font-size: 12px;
			padding: 8px;
			border-radius: 8px;
			animation: fadeIn 0.3s ease-in-out;
		}

		@keyframes fadeIn {
			from {
				opacity: 0;
				transform: translateY(-10px);
			}

			to {
				opacity: 1;
				transform: translateY(0);
			}
		}

		@media (max-width: 480px) {
			.container {
				margin: 20px;
				padding: 30px 20px;
			}
		}
	</style>
</head>

<body>

	<div class="container">
		<div class="form-box">
			<img src="assets/images/logo.png" alt="Logo" />
			<form id="login-form">
				<div class="input-div">
					<i class="fas fa-user"></i>
					<input type="text" id="username" name="username" placeholder="Username" required />
				</div>
				<div class="input-div">
					<i class="fas fa-lock"></i>
					<input type="password" id="password" name="password" placeholder="Password" required />
				</div>
				<button type="submit" class="btn">Login</button>
				<div class="links">
					<a href="https://dropestore.com" target="_blank">More products</a>
					<a href="https://api.whatsapp.com/send?phone=558294229991" target="_blank">Contact us</a>
				</div>
			</form>
			<!-- Error message will be inserted here dynamically -->
		</div>
	</div>

	<!-- jQuery included in the right place -->
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<script>
		document.addEventListener("DOMContentLoaded", function () {
			if (typeof jQuery !== 'undefined') {
				$('#login-form').submit(function (e) {
					e.preventDefault();
					console.log("Submit intercepted");

					$('#login-error').remove();

					var formData = {
						username: $('#username').val(),
						password: $('#password').val()
					};

					$.ajax({
						type: 'POST',
						url: 'function/check-login.php',
						data: formData,
						dataType: 'json',
						success: function (response) {
							if (response.status) {
								window.location.href = 'index.php';
							} else {
								showError(response.message);
							}
						},
						error: function (xhr) {
							let response = xhr.responseJSON;
							let message = response && response.message ? response.message : 'Error trying to login.';
							showError(message);
						}
					});

					function showError(msg) {
						$('<div id="login-error"></div>')
							.text(msg)
							.insertAfter('#login-form')
							.hide()
							.fadeIn();

						setTimeout(() => {
							$('#login-error').fadeOut(300, function () {
								$(this).remove();
							});
						}, 5000);
					}
				});
			} else {
				console.error("jQuery not loaded.");
			}
		});
		</script>
</body>

</html>