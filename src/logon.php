<head>
    <title>予約システム　ログオン</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css?family=Noto+Sans+JP&display=swap" rel="stylesheet">
    <link href="css/signin.css" rel="stylesheet">
    <link href="../example.css" rel="stylesheet">
    <meta http-equiv="content-type" charset="utf-8">
</head>

<form class="form-signin">
  <img class="mb-4" src="img/logo.gif" alt="" width="72" height="72">
  <h1 class="h3 mb-3 font-weight-normal">SSO-KID 認証を行って下さい</h1>
  <label for="inputEmail" class="sr-only">Email address</label>
  <input type="email" id="inputEmail" class="form-control" placeholder="Email address" required autofocus>
  <label for="inputPassword" class="sr-only">Password</label>
  <input type="password" id="inputPassword" class="form-control" placeholder="Password" required>
  <div class="checkbox mb-3">
    <label>
      <input type="checkbox" value="remember-me"> Remember me
    </label>
  </div>
  <button class="btn btn-lg btn-primary btn-block" type="submit" onclick="location.href='gant1.php'">Sign in</button>
  <p class="mt-5 mb-3 text-muted">&copy; ProDevice　2020~</p>
</form>
