<?php include ('includes/header.php'); ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<div class="container-fluid px-4">
    <div class="card mt-4 shadow-sm">
        <div class="card-header">
            <h4 class="mb-0">
                <i class="fas fa-cash-register"></i> Cashier/Staff
                <a href="admins-create.php" class="btn btn-primary float-end"> Add Cashier/Staff </a>
            </h4>
        </div>
        <div class="card-body">
            <?php alertMessage(); ?>
            
            <?php
            $cashier_staff = getAll('cashier_staff');
            if(!$cashier_staff){
                echo '<h4>Something Went Wrong!</h4>';
                return false;
            }
            if(mysqli_num_rows($cashier_staff) > 0){
            ?>
            <div class="table-responsive">
                <table class="table table-striped table-bordered beige-table">
                    <thead> 
                        <tr>
                            <th>First Name</th>
                            <th>Middle Name</th>
                            <th>Last Name</th>
                            <th>Email</th>
                            <th>Username</th>
                            <th>Position</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($cashier_staff as $cashier_staffItem) :?>
                        <?php
                            $isSelf = ((int)($_SESSION['loggedInUser']['user_id'] ?? 0) === (int)$cashier_staffItem['id']);
                            $isSelfOwner = $isSelf && (strcasecmp((string)$cashier_staffItem['position'], 'Owner') === 0);
                        ?>
                        <tr>
                            <td><?= $cashier_staffItem['first_name'] ?></td>
                            <td><?= $cashier_staffItem['middle_name'] ?></td>
                            <td><?= $cashier_staffItem['last_name'] ?></td>
                            <td><?= $cashier_staffItem['email'] ?></td>
                            <td><?= $cashier_staffItem['username'] ?></td>
                            <td><?= $cashier_staffItem['position'] ?></td>
                            <td>
                                <a href="cashier_staff-edit.php?id=<?= $cashier_staffItem['id']; ?>" class="btn btn-success btn-sm">Edit</a>
                                <?php if($isSelfOwner): ?>
                                    <button type="button" class="btn btn-danger btn-sm" disabled title="Owner cannot delete own account">Delete</button>
                                <?php else: ?>
                                    <a href="cashier_staff-delete.php?id=<?= $cashier_staffItem['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table> 
            </div>
            <?php
            } else {
                echo '<h4 class="mb-0">No Record Found</h4>';
            }
            ?>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>
