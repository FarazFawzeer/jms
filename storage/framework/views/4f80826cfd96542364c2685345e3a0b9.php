<?php $__env->startSection('body-attribuet'); ?>
    class="authentication-bg"
<?php $__env->stopSection(); ?>

<style>
    .authentication-bg {
        background: linear-gradient(90deg, #000, #000) !important;
    }
</style>

<?php $__env->startSection('content'); ?>
    <div class="account-pages py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="card border-0 shadow-lg">
                        <div class="card-body p-5">
                            <div class="text-center">
                                <div class="mx-auto mb-4 text-center auth-logo">
                                    <a href="#" class="logo-dark">
                                        <img src="/images/jms.png" height="100" width="200" alt="logo dark">
                                    </a>
                                    <a href="#" class="logo-light">
                                        <img src="/images/jms.png" height="50" alt="logo light">
                                    </a>
                                </div>

                                
                                <img src="<?php echo e(Auth::user()?->image_path ? asset(Auth::user()->image_path) : asset('images/users/avatar-6.jpg')); ?>"
                                    alt="User Avatar" class="rounded-circle mb-3"
                                    style="width:80px;height:80px;object-fit:cover;">

                                <h4 class="fw-bold text-dark mb-2">
                                    Hi ! <?php echo e(Auth::user()?->name ?? 'Guest'); ?>

                                </h4>
                                <p class="text-muted">Enter your password to access the admin.</p>
                            </div>

                            
                            <form action="<?php echo e(route('login')); ?>" method="POST" class="mt-4">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="email" value="<?php echo e(Auth::user()?->email); ?>">

                                <div class="mb-3">
                                    <label class="form-label" for="password">Password</label>
                                    <input type="password" id="password" name="password"
                                        class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                        placeholder="Enter your password">

                                    
                                    <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong><?php echo e($message); ?></strong>
                                        </span>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>

                                
                                <?php if(session('error')): ?>
                                    <div class="alert alert-danger">
                                        <?php echo e(session('error')); ?>

                                    </div>
                                <?php endif; ?>
                                <?php if($errors->has('email')): ?>
                                    <div class="alert alert-danger">
                                        <?php echo e($errors->first('email')); ?>

                                    </div>
                                <?php endif; ?>

                                <div class="mb-1 text-center d-grid">
                                    <button class="btn btn-dark btn-lg fw-medium" type="submit">Unlock</button>
                                </div>
                            </form>

                        </div>
                    </div>

                    <p class="text-center mt-4 text-white text-opacity-50">
                        Not you? return
                        <a href="<?php echo e(route('login')); ?>" class="text-decoration-none text-white fw-bold">Sign In</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.base', ['subtitle' => 'Lock Screen'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Personal Projects\Infotech\jms\resources\views/auth/lock-screen.blade.php ENDPATH**/ ?>