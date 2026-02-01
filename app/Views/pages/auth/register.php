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
                    <h2 class="fs-20 fw-bolder mb-4">Register</h2>
                    <h4 class="fs-13 fw-bold mb-2">Create your account</h4>
                    <p class="fs-12 fw-medium text-muted">Join <strong>Nelel</strong> web applications today.</p>

                    <form action="<?= url_to('AuthController::attemptRegister') ?>" method="post" class="w-100 mt-4 pt-2">
                        <?= csrf_field() ?>
                        
                        <div class="mb-4">
                            <input type="email" class="form-control" name="email" placeholder="Email Address" value="<?= old('email') ?>" required>
                            <small class="text-danger"><?= session('error.email') ?></small>
                        </div>
                        
                        <div class="mb-4">
                            <input type="text" class="form-control" name="username" placeholder="Username" value="<?= old('username') ?>" required>
                            <small class="text-danger"><?= session('error.username') ?></small>
                        </div>

                        <div class="mb-4">
                            <input type="password" class="form-control" name="password" placeholder="Password" value="" required autocomplete="new-password">
                            <small class="text-danger"><?= session('error.password') ?></small>
                        </div>

                        <div class="mb-4">
                            <input type="password" class="form-control" name="pass_confirm" placeholder="Repeat Password" value="" required autocomplete="new-password">
                            <small class="text-danger"><?= session('error.pass_confirm') ?></small>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">Register</button>

                        <div class="mt-4 text-center">
                            <p class="mb-0">Already have an account? <a href="<?= url_to('login') ?>" class="fw-bold text-primary">Login</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- SweetAlert Logic -->
<?php if (session()->getFlashdata('error') && !is_array(session()->getFlashdata('error'))) : ?>
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
<!-- Note: Array errors are handled inline below inputs usually, or we can sweetalert join them -->

<?= $this->endSection() ?>
