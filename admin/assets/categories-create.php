<?php include ('includes/header.php'); ?>

<style>
    .beige-card {;
        background-color:rgb(255, 255, 255); 
        color:rgb(0, 0, 0);
    }
    .beige-card-header {
        background-color: #d9c8b4 !important; 
        color: #3b2f2f; 
    }
    .form-control {
        background-color: #f8f1e3; 
        border: 1px solid #3b2f2f;
        color: #a0896b;
    }
    .form-control:focus {
        background-color:rgb(255, 255, 255); 
        border-color: #a0896b;
        box-shadow: 0 0 5px rgba(160, 137, 107, 0.5);
    }
    .btn-primary {
        background-color: #8c7355;
        border-color: #a97d5d;
    }
    .btn-primary:hover {
        background-color:rgb(255, 255, 255);
        color: #8c7355;
    }
    .btn-secondary {
        background-color: #8c7355;
        border-color: #a97d5d;
    }
    .container-fluid {
        border-radius: 25px;
        background-color:rgb(255, 255, 255); 
        padding: 20px;
    }
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<div class="container-fluid px-4">
    <div class="card mt-4 shadow-sm beige-card">
        <div class="card-header beige-card-header">
            <h4 class="mb-0">
                <i class="fas fa-cash-register"></i> Add Category
                <a href="categories.php" class="btn btn-secondary float-end"> Back </a>
            </h4>
        </div>
        <div class="card-body">

            <?php alertMessage(); ?>

            <form action="code.php" method="POST">

                <div class="row">
                <div class="col-md-8 mb-3">
                        <label for="">Name *</label>
                        <input type="text" name="name" required class="form-control" />
                    </div>
                <div class="col-md-12 mb-3">
                        <label for="">Description </label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
<style>

  .status-container {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    font-family: Arial, sans-serif;
    font-size: 16px;
  }

  .status-checkbox {
    display: none;
  }

  .custom-checkbox {
    width: 40px;
    height: 20px;
    background-color: green;
    border-radius: 20px;
    position: relative;
    transition: 0.3s;
  }

  .custom-checkbox::before {
    content: "";
    width: 18px;
    height: 18px;
    background-color: white;
    border-radius: 50%;
    position: absolute;
    top: 1px;
    left: 2px;
    transition: 0.3s;
  }

  .status-checkbox:checked + .custom-checkbox {
    background-color:rgb(201, 22, 22);
  }

  .status-checkbox:checked + .custom-checkbox::before {
    transform: translateX(20px);
  }

  .status-label {
    font-weight: bold;
  }
</style>

    <label class="status-container">
    <input type="checkbox" class="status-checkbox" name="status">
    <div class="custom-checkbox"></div>
    <span class="status-label">Available</span>
    </label>

<script>
    const checkbox = document.querySelector(".status-checkbox");
    const label = document.querySelector(".status-label");

    checkbox.addEventListener("change", function () {
        label.textContent = this.checked ? "Not Available" : "Available";
    });
</script>
                    <div class="col-md-6 mb-3 text-end">
                        <br/>
                        <button type="submit" name="saveCategory" class="btn btn-primary">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>