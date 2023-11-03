<li class="<?php echo setRoleForAnchorTag('profile', 2); ?> hover-collapse" div-target="profileItems" style="bottom: 15px;position: absolute;">
    <a data-bs-toggle="collapse" href="#profileItems">
        <!-- <i class="tim-icons icon-lock-circle"></i> -->
        <i class="fa fa-user-circle" style="font-size:20px"></i>
        <p> &nbsp; </p>
    </a>

    <div class="collapse menu-l1-view" id="profileItems" style="bottom: 1px;">


        <ul class="nav">
            <li class="<?php echo setRoleForAnchorTag('profileEdit', 2); ?> <?php echo setActiveClass('windowtype', 'profileEdit'); ?>">
                <a href="javascript:void(0)" id="profDispButt">
                    <span class="sidebar-normal"> Profile </span>
                </a>
            </li>

            <li class="">
                <a onclick="GoToResetPassword()" class="">
                    <span class="sidebar-normal"> Reset Password </span>
                </a>
            </li>

            <!-- <li class="<?php echo setRoleForAnchorTag('subscription', 2); ?> <?php echo setActiveClass('windowtype', 'subscription'); ?>">
                            <a href="../Subscription">
                                <span class="sidebar-normal">
                                Subscription
                                </span>
                            </a>
                        </li> -->

            <li class="">
                <a href="<?php echo "//" . $_SERVER['HTTP_HOST'] . "/Dashboard/logout.php" ?>" class="">

                    <span data-qa="logOut" class="sidebar-normal">Log out</span>
                </a>
            </li>
        </ul>
    </div>
</li>

<footer class="footer white-content">
    <div class="container-fluid">
        <div class="copyright">
            &copy;
            <script>
                document.write(new Date().getFullYear())
            </script> Nanoheal
        </div>
    </div>
</footer> 