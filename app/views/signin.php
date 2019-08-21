<!DOCTYPE html>
<html lang="en">
     <head>
          <meta charset="utf-8">
          <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
          <meta name="description" content="">
          <meta name="author" content="Fiand T">
          <title>Signin to HTMLRun - Test your JavaScript, CSS or HTML online</title>
          <link href="<?= APPURL ?>/favicon-32x32.png" rel="icon" type="image/png" sizes="32x32">
          <link href="<?= APPURL ?>/favicon-96x96.png" rel="icon" type="image/png" sizes="96x96">
          <link href="<?= APPURL ?>/favicon-16x16.png" rel="icon" type="image/png" sizes="16x16">
          <link href="<?= APPURL ?>/assets/css/plugins.css?v=<?= rand(1,9999) ?>" rel="stylesheet" type="text/css" />
          <link href="<?= APPURL ?>/assets/css/core.css?v=<?= rand(1,9999) ?>" rel="stylesheet" type="text/css" />
     </head>
     <body class="signin text-center">
          <form class="form-signin" action="" method="post">
               <input type="hidden" name="action" value="signin">
               <a href="<?= APPURL ?>">
                    <img class="mb-4" src="<?= APPURL ?>/assets/img/favicon.png" alt="" width="72" height="72">
               </a>
               <h1 class="h5 mb-3 font-weight-normal">Login to your account</h1>

               <?php if (Input::post("action") == "signin"): ?>
               <div class="alert alert-danger" role="alert">
                    <?= "Login credentials didn't match!" ?>
               </div>
               <?php endif; ?>
               
               <label for="username" class="sr-only">Email address</label>

               <input type="text" name="username" id="username" 
                      class="form-control" placeholder="Username" 
                      required autofocus value="<?= Input::post("username") ?>">

               <label for="password" class="sr-only">Password</label>
               <input type="password" name="password" id="password" 
                      class="form-control" placeholder="Password" required>
                      
                    <div class="text-center mt-3 mb-3 link">
                         <a href="<?= APPURL ?>/reset">Forgot password</a> | 
                         <a href="<?= APPURL ?>/lost_username">Retrieve username</a>
                    </div>
               <button class="btn btn-primary btn-block" type="submit">Sign in</button>
               <p class="mt-3 mb-3 text-muted">Don't you have an account?</p>
               <a class="btn btn-dark btn-block" href="<?= APPURL ?>/signup">Create a free account</a>
          </form>
     </body>
</html>