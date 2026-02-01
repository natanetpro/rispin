<?= $this->extend('layouts/guest') ?>

<?= $this->section('content') ?>
<main class="auth-minimal-wrapper">
    <div class="auth-minimal-inner">
        <div class="minimal-card-wrapper">
            <div class="card mb-4 mt-5 mx-4 mx-sm-0 position-relative">
                <div class="wd-50 bg-white p-2 rounded-circle shadow-lg position-absolute translate-middle top-0 start-50">
                    <img src="<?= base_url('assets/images/logo-abbr.png') ?>" alt="" class="img-fluid">
                </div>
                <div class="card-body p-sm-5">
                    <h2 class="fs-20 fw-bolder mb-4">Login</h2>
                    <h4 class="fs-13 fw-bold mb-2">Login to your account</h4>
                    <p class="fs-12 fw-medium text-muted">Thank you for get back <strong>Nelel</strong> web applications, let's access our the best recommendation for you.</p>

                    <form action="<?= url_to('AuthController::attemptLogin') ?>" method="post" class="w-100 mt-4 pt-2">
                        <?= csrf_field() ?>
                        <div class="mb-4">
                            <!-- Note: Changed type to text and name to nama to match DB column and Controller logic -->
                            <input type="text" class="form-control" name="login" placeholder="Username" required autofocus>
                        </div>
                        <div class="mb-3">
                            <input type="password" class="form-control" name="password" placeholder="Password" required>
                        </div>
                        
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <?php if ($config->allowRemembering): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember" <?php if (old('remember')) : ?> checked <?php endif ?>>
                                    <label class="form-check-label" for="remember">
                                        Remember Me
                                    </label>
                                </div>
                            <?php endif; ?>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Login</button>

                        <?php if ($config->allowRegistration) : ?>
                            <div class="mt-4 text-center">
                                <p class="mb-0">Don't have an account? <a href="<?= url_to('register') ?>" class="fw-bold text-primary">Register</a></p>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- SweetAlert Logic -->
<?php if (session()->getFlashdata('error')) : ?>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: '<?= session()->getFlashdata('error') ?>',
            });
        });
    </script>
<?php endif; ?>

<?php if (session()->getFlashdata('success')) : ?>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '<?= session()->getFlashdata('success') ?>',
            });
        });
    </script>
<?php endif; ?>

<?= $this->endSection() ?>