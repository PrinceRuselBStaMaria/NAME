<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="style.css"/>
    <title>Admin Login</title>
    <link rel="stylesheet" href="style.css">
</head>
  
<body>
    <div class="wrapper"> 
    <form action="login_process.php" method="POST">
    <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
    <h1>Admin Login</h1>

    <div class="input-box">
        <input type="text" name="username" placeholder="username" required>
        <i class='bx bxs-user'></i>
    </div>

    <div class="input-box">
        <input type="password" name="password" placeholder="password" required>
        <i class='bx bxs-lock'></i>
    </div>
    
    <button type="submit" class="btn">Login</button>
</form>

    </div>
    

    
</body>
</html>