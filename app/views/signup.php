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
               <input type="hidden" name="action" value="signup">
               <a href="<?= APPURL ?>">
                    <img class="mb-4" src="<?= APPURL ?>/assets/img/favicon.png" alt="" width="72" height="72">
               </a>
               <h1 class="h5 mb-3 font-weight-normal">Create your account</h1>

               <?php if (!empty($FormErrors)): ?>
                        <?php foreach ($FormErrors as $error): ?>
                         <div class="alert alert-danger" role="alert">
                               <?= $error ?>
                         </div>
                        <?php endforeach; ?>
                <?php endif; ?>
               
               <div class="form-group">
                    <input type="text" placeholder="Fullname" class="form-control" id="fullname" 
                           name="fullname" value="<?= Input::post("fullname") ?>" required>
               </div>
               <div class="form-group">
                    <input type="text" placeholder="Username" class="form-control" id="username" 
                           name="username" value="<?= Input::post("username") ?>" required>
               </div>
               <div class="form-group">
                    <input type="email" placeholder="Email" class="form-control" id="email" 
                           name="email" value="<?= Input::post("email") ?>"  required>
               </div>
               <div class="form-group">
                    <input type="password" placeholder="Password" class="form-control" 
                           id="password" name="password"  required>
               </div>

               <div class="form-group">
                    <input type="password" placeholder="Confirm Password" class="form-control" 
                           id="password-confirm" name="password-confirm"  required>
               </div>
               
               <div class="text-center mt-3 mb-3 link">
                    <small>By signing up to HTMLRun you confirm that you agree with the <a href="<?= APPURL ?>/term-services" target="_blank">member terms and conditions</a></small>
               </div>
               <button class="btn btn-primary btn-block" type="submit">Create my free account</button>
               
               <div class="mt-3 mb-3 link">
                    <a href="<?= APPURL ?>/signin">Do you already have an account?</a>
               </div>
          </form>
     </body>
</html>