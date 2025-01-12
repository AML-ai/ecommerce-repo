<header class="main-header">
    <nav class="navbar navbar-static-top">
        <div class="container">
            <div class="navbar-header">
                <a href="index.php" class="navbar-brand"><b>Ecommerce</b>Site</a>
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse"
                        data-target="#navbar-collapse">
                    <i class="fa fa-bars"></i>
                </button>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse pull-left" id="navbar-collapse">
                <ul class="nav navbar-nav">
                    <li><a href="index.php">HOME</a></li>
                    <li><a href="category.php">Products</a></li>
                    <?php
                    if (isset($_SESSION['user'])) {
                        echo ' <li><a href="transactions.php">
                            Your Transactions
                        </a></li>';
                    }
                    ?>

                </ul>

            </div>
            <!-- /.navbar-collapse -->
            <!-- Navbar Right Menu -->
            <div class="navbar-custom-menu">
                <ul class="nav navbar-nav">
                    <li><a href="cart_view.php">
                            <i class="fa fa-lg fa-shopping-cart" style="color: #79f97c"></i>
                        </a></li>

                    <?php
                    if (isset($_SESSION['user'])) {
                        $image = 'https://ecommerce-assetsbucket.s3.eu-north-1.amazonaws.com/profiles/' . (!empty($user['photo']) ? $user['photo'] : 'profile.jpg');
                        echo '
                <li class="dropdown user user-menu">
                  <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                    <img src="' . $image . '" class="user-image" alt="User Image">
                    <span class="hidden-xs">' . $user['firstname'] . ' ' . $user['lastname'] . '</span>
                  </a>
                  <ul class="dropdown-menu">
                    <!-- User image -->
                    <li class="user-header">
                      <img src="' . $image . '" class="img-circle" alt="User Image">

                      <p>
                        ' . $user['firstname'] . ' ' . $user['lastname'] . '
                        <small>Member since ' . date('M. Y', strtotime($user['created_on'])) . '</small>
                      </p>
                    </li>
                    <li class="user-footer">
                      <div class="pull-left">
                        <a href="#edit" class="btn btn-default btn-flat"  data-toggle="modal">Edit Profile</a>
                      </div>
                      <div class="pull-right">
                        <a href="logout.php" class="btn btn-default btn-flat">Sign out</a>
                      </div>
                    </li>
                  </ul>
                </li>
              ';
                    } else {
                        echo "
                <li><a href='login.php'>LOGIN</a></li>
                <li><a href='signup.php'>SIGNUP</a></li>
              ";
                    }
                    ?>
                </ul>
            </div>
        </div>
    </nav>
</header>
<?php include 'includes/profile_modal.php'; ?>
