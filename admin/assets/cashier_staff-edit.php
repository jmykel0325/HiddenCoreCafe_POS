<?php include ('includes/header.php'); ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<div class="container-fluid px-4">
    <div class="card mt-4 shadow-sm beige-card">
        <div class="card-header beige-card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0 d-flex align-items-center gap-2">
                <i class="fas fa-cash-register"></i>
                <span>Edit Cashier/Staff</span>
            </h4>
            <a href="admins.php" class="btn btn-secondary">Back</a>
        </div>
        <div class="card-body">
            <?php alertMessage(); ?>

            <form action="code.php" method="POST">

                <?php
                    if(isset($_GET['id'])){
                        if($_GET['id'] != ''){
                            
                            $adminId = $_GET['id'];
                        }else{
                            echo '<h5>No Id Found</h5>';
                            return false;
                        }
                    }
                    else{
                        echo '<h5>No Id given in params</h5>';
                            return false;
                    }

                     $adminData = getById('cashier_staff',$adminId);
                     if($adminData){
                        
                        if($adminData['status']== 200){
                            $isSelfOwnerEdit = ((int)($_SESSION['loggedInUser']['user_id'] ?? 0) === (int)$adminData['data']['id'])
                                && (strcasecmp((string)$adminData['data']['position'], 'Owner') === 0);
                            ?>
                            <input type="hidden" name="adminId" value="<?= $adminData['data']['id']; ?>" >
                            <?php if($isSelfOwnerEdit): ?>
                                <input type="hidden" name="position" value="<?= htmlspecialchars($adminData['data']['position']) ?>">
                            <?php endif; ?>

                            <div class="row g-3">
                <div class="col-md-6">
                        <label class="form-label mb-1">First Name *</label>
                        <input type="text" name="first_name" value="<?= htmlspecialchars($adminData['data']['first_name']) ?>" required class="form-control" />
                    </div>
                <div class="col-md-6">
                        <label class="form-label mb-1">Middle Name</label>
                        <input type="text" name="middle_name" value="<?= htmlspecialchars($adminData['data']['middle_name']) ?>" class="form-control" />
                    </div>
                <div class="col-md-6">
                        <label class="form-label mb-1">Last Name *</label>
                        <input type="text" name="last_name" value="<?= htmlspecialchars($adminData['data']['last_name']) ?>" required class="form-control" />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label mb-1">Email *</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($adminData['data']['email']) ?>" required class="form-control" />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label mb-1">Username *</label>
                        <input type="text" name="username" value="<?= htmlspecialchars($adminData['data']['username']) ?>" required class="form-control" />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label mb-1">Password</label>
                        <input type="password" name="password" class="form-control" />
                        <small class="text-muted">Leave blank to keep current password.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label mb-1">Position *</label>
                        <select name="position" required class="form-select" <?= $isSelfOwnerEdit ? 'disabled' : '' ?>>
                            <option value="Cashier" <?= ($adminData['data']['position'] === 'Cashier') ? 'selected' : '' ?>>Cashier</option>
                            <option value="Staff" <?= ($adminData['data']['position'] === 'Staff') ? 'selected' : '' ?>>Staff</option>
                            <option value="Owner" <?= ($adminData['data']['position'] === 'Owner') ? 'selected' : '' ?>>Owner</option>
                        </select>
                        <?php if($isSelfOwnerEdit): ?>
                            <small class="text-muted">Owner cannot change their own position.</small>
                        <?php endif; ?>
                    </div>
                                <div class="col-12 d-flex justify-content-end mt-2">
                                    <button type="submit" name="updateCashier/Staff" class="btn btn-primary">Update</button>
                                </div>
                            </div>
                            <?php
                        }
                        else{
                            echo '<h5>'.$adminData['message'].'</h5>';  }
                     }
                     else{
                            echo 'Something went wrong';
                            return false;
                        }           
                ?>



            </form>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>
