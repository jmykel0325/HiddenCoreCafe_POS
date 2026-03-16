<?php include ('includes/header.php'); ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<div class="container-fluid px-4">
    <div class="card mt-4 shadow-sm beige-card">
        <div class="card-header beige-card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0 d-flex align-items-center gap-2">
                <i class="fas fa-cash-register"></i>
                <span>Add Cashier/Staff</span>
            </h4>
            <a href="admins.php" class="btn btn-secondary">Back</a>
        </div>
        <div class="card-body">
            <?php alertMessage(); ?>

            <form action="code.php" method="POST">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label mb-1">First Name *</label>
                        <input type="text" name="first_name" required class="form-control" />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label mb-1">Middle Name</label>
                        <input type="text" name="middle_name" class="form-control" />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label mb-1">Last Name *</label>
                        <input type="text" name="last_name" required class="form-control" />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label mb-1">Email *</label>
                        <input type="email" name="email" required class="form-control" />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label mb-1">Username *</label>
                        <input type="text" name="username" required class="form-control" />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label mb-1">Password *</label>
                        <input type="password" name="password" required class="form-control" />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label mb-1">Position *</label>
                        <select name="position" required class="form-select">
                            <option value="" selected disabled>Select position</option>
                            <option value="Cashier">Cashier</option>
                            <option value="Staff">Staff</option>
                            <option value="Owner">Owner</option>
                        </select>
                    </div>
                    <div class="col-12 d-flex justify-content-end mt-2">
                        <button type="submit" name="SaveCashier/Staff" class="btn btn-primary">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>
