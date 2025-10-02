
<?php 



if ($_SERVER['REQUEST_METHOD'] === 'POST' &&  isset($_POST['username']) && isset($_POST['password']) ) {
$username =$_POST['username'];
$password =$_POST['password'];

if (preg_match('/^[\p{L}0-9@#$%^&*]{6,20}$/u', $password) && preg_match('/^[a-zA-Z0-9_]{6,20}$/', $username)) { 
	if($password == $pass_admin && $username ==$user_admin ){
		$_SESSION['user_admin'] = $username;
		$_SESSION['pass_admin'] = $password;	
		header("location: /look");
	
	}

    }


}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>صفحة تسجيل الدخول</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background-color: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
        }
        .login-container h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        .input-group {
            margin-bottom: 20px;
        }
        .input-group label {
            display: block;
            margin-bottom: 5px;
            color: #555;
        }
        .input-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
            color: #333;
        }
        .input-group input:focus {
            border-color: #007BFF;
            outline: none;
        }
        .login-btn {
            width: 100%;
            padding: 10px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .login-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

    <div class="login-container">
        <h2>تسجيل الدخول</h2>
        <form action="#" method="POST">
            <div class="input-group">
                <label for="username">اسم المستخدم</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="input-group">
                <label for="password">كلمة المرور</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="login-btn">دخول</button>
        </form>
    </div>

</body>
</html>
