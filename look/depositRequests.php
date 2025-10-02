
<?php
function addPaymentMessage($conn, $userId, $amount) {
    $title = "🎉 Congratulations! 🎉";

    $message = "
    <div class='modal fade' id='paymentModal' tabindex='-1' aria-labelledby='paymentModalLabel' aria-hidden='true'>
      <div class='modal-dialog modal-dialog-centered'>
        <div class='modal-content payment-modal-content'>
          <div class='modal-header border-0'>
            <h5 class='modal-title w-100 text-center' id='paymentModalLabel'>
              <i class='bi bi-gift-fill'></i> {$title} <i class='bi bi-trophy-fill'></i>
            </h5>
            <button type='button' class='btn-close position-absolute end-0 top-0 m-3' data-bs-dismiss='modal' aria-label='Close'></button>
          </div>
          <div class='modal-body'>
            <p class='lead'>
              <i class='bi bi-cash-coin'></i> Your payment of 
              <strong class='text-warning'>{$amount} RUB</strong> 
              has been <span class='text-success fw-bold'>successfully received</span> ✅
            </p>
            <p class='mt-3'>
              <i class='bi bi-stars'></i> Thank you for using our platform.<br>
              We wish you a <span class='text-info fw-bold'>wonderful experience</span> 💙
            </p>
          </div>
          <div class='modal-footer border-0 justify-content-center'>
            <button type='button' class='btn btn-glow' data-bs-dismiss='modal'>
              <i class='bi bi-check-circle-fill'></i> OK, Thanks
            </button>
          </div>
        </div>
      </div>
    </div>

    <script>
      window.addEventListener('DOMContentLoaded', () => {
        const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
        modal.show();
      });
    </script>

    <style>
      .payment-modal-content {
        background: linear-gradient(145deg, #6a11cb 0%, #2575fc 100%);
        border-radius: 18px;
        padding: 25px;
        color: #fff;
        text-align: center;
        box-shadow: 0 12px 35px rgba(0,0,0,0.25);
        animation: popUp 0.4s ease-in-out;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      }
      .payment-modal-content h5 {
        font-size: 26px;
        font-weight: bold;
        color: #fff200;
      }
      .payment-modal-content p {
        font-size: 17px;
        margin: 12px 0;
        color: #f1f1f1;
      }
      .btn-glow {
        background: linear-gradient(90deg, #00ff87, #60efff);
        border: none;
        color: #000;
        font-weight: bold;
        font-size: 16px;
        padding: 10px 28px;
        border-radius: 50px;
        transition: all 0.3s ease;
        box-shadow: 0 0 18px rgba(255, 255, 255, 0.5);
      }
      .btn-glow:hover {
        box-shadow: 0 0 28px rgba(255, 255, 255, 0.8);
        transform: scale(1.05);
      }
      @keyframes popUp {
        from { transform: scale(0.9); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
      }
    </style>
    ";

    // إدخال الرسالة في قاعدة البيانات
    $sql = "INSERT INTO message_admin (user_id, title, message_content) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $userId, $title, $message);
    $stmt->execute();
    $stmt->close();
}
?>


 <?php



 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id'], $_POST['action'], $_POST['val'])) {
        $id = intval($_POST['id']);
        $action = $_POST['action'];
        $val = floatval($_POST['val']);

        // جلب بيانات العملية
        $query = "SELECT * FROM payments WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
			
        if ($result->num_rows > 0) {
            $payment = $result->fetch_assoc();
            $user_id = $payment['user_id'];
			$status = $payment['payment_status'];
						$query = "SELECT * FROM users WHERE user_id = ?";
						$stmt = $conn->prepare($query);
						$stmt->bind_param("i",$user_id );
						$stmt->execute();
						$result = $stmt->get_result();
						if ($result->num_rows > 0) {
						$payment_u = $result->fetch_assoc();
						$actual_balance = $payment_u['payments_money'];
						
						$remaining_balance = bcsub((string)$actual_balance, (string)$val, 2); // دقة رقمين بعد الفاصلة

						if ((float)$remaining_balance >= 50) {
							$user_active = true;
						} else {
							$user_active =false;
						}
						
						}
						
				
            if ($action === 'OK') {
				if($status == 'ok'){ 
				}else{
                $update = $conn->prepare("UPDATE payments SET payment_status = 'ok' , money_value = ? WHERE id = ?");
                $update->bind_param("di",$val, $id);
                $update->execute();
				$money_user = $actual_balance + $val;
				if ($money_user >= 50  ) {
					$update_user = $conn->prepare("UPDATE users SET is_active = 1 ,payments_money = payments_money + ? WHERE user_id = ?");
					} else {
					$update_user = $conn->prepare("UPDATE users SET payments_money = payments_money + ? WHERE user_id = ?");
					}
                $update_user->bind_param("di",$val, $user_id);
                $update_user->execute();
				
				$update_user = $conn->prepare("UPDATE user_money SET advertising_balance = advertising_balance + ? WHERE user_id = ?");
                $update_user->bind_param("di", $val, $user_id);
                $update_user->execute();
				addPaymentMessage($conn, $user_id, $val);
				$referrer_id = null;
				$referrer_money = $val/100*5;
				$stmt = $conn->prepare("SELECT referrer_id FROM referrals WHERE referred_id = ? ");
				$stmt->bind_param("i", $user_id);
				$stmt->execute();
				$stmt->bind_result($referrer_id);
				$stmt->fetch();
				$stmt->close();
				if ($referrer_id) {
					// أضف 0.01 إلى حساب referrer
					$updateStmt = $conn->prepare("UPDATE user_money SET available_to_withdraw = available_to_withdraw + ? WHERE user_id = ?");
					$updateStmt->bind_param("di",$referrer_money, $referrer_id);
					$updateStmt->execute();
					$updateStmt->close();
					$updateStmt1 = $conn->prepare("UPDATE referrals SET is_active = 1 , earnings = earnings + ? WHERE referred_id = ?");
					$updateStmt1->bind_param("di",$referrer_money, $user_id);
					$updateStmt1->execute();
					$updateStmt1->close();
				}
				}
				$_SESSION['form_message'] = "<div class='alert alert-success'> ❌ ✅ تمت الموافقة على العملية.".$user_active."</div>";

            } elseif ($action === 'NO') {
                
				if($status == 'ok'){ 	
					$update = $conn->prepare("UPDATE payments SET payment_status = 'no' , money_value = ? WHERE id = ?");
						$update->bind_param("di",$val, $id);
						$update->execute();

						if ($user_active){
							$update_user = $conn->prepare("UPDATE users SET is_active = 1 ,payments_money = payments_money - ? WHERE user_id = ?");
						}else{
							$update_user = $conn->prepare("UPDATE users SET is_active = 0 ,payments_money = payments_money - ? WHERE user_id = ?");
							}					
						$update_user->bind_param("di",$val, $user_id);
						$update_user->execute();
						$update_user = $conn->prepare("UPDATE user_money SET advertising_balance = advertising_balance - ? WHERE user_id = ?");
						$update_user->bind_param("di", $val, $user_id);
						$update_user->execute();
						$delete_sql = "DELETE FROM message_admin WHERE user_id = ?";
						$del_stmt = $conn->prepare($delete_sql);
						$del_stmt->bind_param("i", $user_id);
						$del_stmt->execute();
						$referrer_id = null;
						$referrer_money = $val/100*5;
						$stmt = $conn->prepare("SELECT referrer_id FROM referrals WHERE referred_id = ? ");
						$stmt->bind_param("i", $user_id);
						$stmt->execute();
						$stmt->bind_result($referrer_id);
						$stmt->fetch();
						$stmt->close();
						if ($referrer_id) {
							// أضف 0.01 إلى حساب referrer
							$updateStmt = $conn->prepare("UPDATE user_money SET available_to_withdraw = available_to_withdraw - ? WHERE user_id = ?");
							$updateStmt->bind_param("di",$referrer_money, $referrer_id);
							$updateStmt->execute();
							$updateStmt->close();
							if ($user_active){
							$updateStmt1 = $conn->prepare("UPDATE referrals SET is_active = 1, earnings = earnings - ? WHERE referred_id = ?");
							}else{
							$updateStmt1 = $conn->prepare("UPDATE referrals SET is_active = 0, earnings = earnings - ? WHERE referred_id = ?");
							}
							$updateStmt1->bind_param("di",$referrer_money, $user_id);
							$updateStmt1->execute();
							$updateStmt1->close();
						}
						
						
						
						
						
				
				}else{
					$update = $conn->prepare("UPDATE payments SET payment_status = 'no' WHERE id = ?");
					$update->bind_param("i", $id);
					$update->execute();
				
				}	

				$_SESSION['form_message'] = "<div class='alert alert-success'>❌ تم رفض العملية</div>";
            } elseif ($action === 'Waiting') {
			
			
				if($status == 'ok'){ 


							
						$update = $conn->prepare("UPDATE payments SET payment_status = 'pending' , money_value = ? WHERE id = ?");
						$update->bind_param("di",$val, $id);
						$update->execute();

						if ($user_active){
						$update_user = $conn->prepare("UPDATE users SET is_active = 1 ,payments_money = payments_money - ? WHERE user_id = ?");
						}else{
						$update_user = $conn->prepare("UPDATE users SET is_active = 0 ,payments_money = payments_money - ? WHERE user_id = ?");
						}					
						$update_user->bind_param("di",$val, $user_id);
						$update_user->execute();
						$update_user = $conn->prepare("UPDATE user_money SET advertising_balance = advertising_balance - ? WHERE user_id = ?");
						$update_user->bind_param("di", $val, $user_id);
						$update_user->execute();
						$delete_sql = "DELETE FROM message_admin WHERE user_id = ?";
						$del_stmt = $conn->prepare($delete_sql);
						$del_stmt->bind_param("i", $user_id);
						$del_stmt->execute();
						$referrer_id = null;
						$referrer_money = $val/100*5;
						$stmt = $conn->prepare("SELECT referrer_id FROM referrals WHERE referred_id = ? ");
						$stmt->bind_param("i", $user_id);
						$stmt->execute();
						$stmt->bind_result($referrer_id);
						$stmt->fetch();
						$stmt->close();
						if ($referrer_id) {
							// أضف 0.01 إلى حساب referrer
							$updateStmt = $conn->prepare("UPDATE user_money SET available_to_withdraw = available_to_withdraw - ? WHERE user_id = ?");
							$updateStmt->bind_param("di",$referrer_money, $referrer_id);
							$updateStmt->execute();
							$updateStmt->close();
							if ($user_active){
							$updateStmt1 = $conn->prepare("UPDATE referrals SET is_active = 1, earnings = earnings - ? WHERE referred_id = ?");
							}else{
							$updateStmt1 = $conn->prepare("UPDATE referrals SET is_active = 0, earnings = earnings - ? WHERE referred_id = ?");
							}
							$updateStmt1->bind_param("di",$referrer_money, $user_id);
							$updateStmt1->execute();
							$updateStmt1->close();
						}
						
						
						
						
				
				}else{
					$update = $conn->prepare("UPDATE payments SET payment_status = 'pending' WHERE id = ?");
					$update->bind_param("i", $id);
					$update->execute();
				
				}	


            
				$_SESSION['form_message'] = "<div class='alert alert-success'> ⏳ تم إرجاع الطلب إلى قيد الانتظار</div> ";

            } else {
               
				$_SESSION['form_message'] = "<div class='alert alert-danger text-center'>❗ نوع إجراء غير معروف..</div>";
            }
        } else {
          
			$_SESSION['form_message'] = "<div class='alert alert-danger text-center'❌ لم يتم العثور على الطلب.</div>";
        }
    } else {
        
		$_SESSION['form_message'] = "<div class='alert alert-danger text-center'>⚠️ بيانات ناقصة.</div>";
    }
}


if (isset($_GET['depositRequests']) && isset($_GET['delete_id']) ){
			if (filter_var($_GET['delete_id'], FILTER_VALIDATE_INT) !== false) {
			$stmt = $conn->prepare("DELETE FROM payments WHERE id = ? "); /////////////////				
			$stmt->bind_param("i",$_GET['delete_id']);
			$stmt->execute();
			}
			?>
<div class="msg">تم الحذف بنجاح</div><script type="text/javascript">setTimeout(function() {window.location.href ="?depositRequests";}, 1000);</script>
<?php } ?>		





  <style>
  /* تنسيقات عامة للرسائل */
.alert {
  padding: 15px;
  margin: 20px auto;
  border-radius: 8px;
  width: 90%;
  max-width: 600px;
  font-family: Arial, sans-serif;
  font-size: 16px;
  direction: rtl;
  text-align: right;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

/* الرسالة الناجحة */
.alert-success {
  background-color: #e6f9e6;
  border: 1px solid #27ae60;
  color: #2e7d32;
}

/* رسالة الخطأ */
.alert-danger {
  background-color: #ffe6e6;
  border: 1px solid #e74c3c;
  color: #c0392b;
}

/* توسيط النص (اختياري لبعض الرسائل) */
.text-center {
  text-align: center;
}

	.msg{
			background-color: #020202;
		color: #59f11a;
		font-size: 20px;
	  
		text-align: center;
		padding: 30px;
		}
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f4f4f4;
            text-align: center;
            padding: 20px;
        }

        /* الحاوية الخاصة بالأزرار */
        .buttons-container {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        /* تصميم الأزرار */
        .btn {
            padding: 15px 25px;
            font-size: 20px;
            border: none;
            cursor: pointer;
            border-radius: 8px;
            transition: 0.3s;
            color: white;
        }

        .pending {
            background-color: orange;
        }

        .approved {
            background-color: green;
        }

        .rejected {
            background-color: red;
        }

        .btn:hover {
            opacity: 0.8;
        }

       


        /* استجابة الهواتف */
        @media (max-width: 600px) {
            .buttons-container {
                flex-direction: column;
                gap: 10px;
            }
        }
		
/* تصميم المربع */
.modal {
   /* إخفاء المربع افتراضيًا */
  position: fixed;
  z-index: 1000;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  display: none;
}

.modal-content {
	
    background-color: #dceddf;
    padding: 20px;
    border-radius: 10px;
    text-align: center;
    
    width: 350px;
    box-shadow: 0 5px 15px rgb(0 0 0 / 30%);
    font-family: Arial, sans-serif;
}

.modal-content h2 {
  color: #333;
  margin-bottom: 15px;
  font-size: 20px;
}
.modal-content h3 {
  color: #333;
  margin-bottom: 15px;
  font-size: 20px;
}


.buttons {
  display: flex;
  justify-content: space-between;
}

.buttons button {
  width: 30%;
    padding: 10px;
    font-size: 16px;
    border: none;
    margin: 0px 3px 0px 3px;
    border-radius: 5px;
    cursor: pointer;
    transition: 0.3s;
}

/* زر موافق */
.accept-btn {
  background-color: #4caf50;
  color: white;
}

.accept-btn:hover {
  background-color: #45a049;
}

/* زر رفض */
.reject-btn {
  background-color: #f44336;
  color: white;
}

.reject-btn:hover {
  background-color: #e53935;
}

/* زر إلغاء */
.cancel-btn {
  background-color: #555;
  color: white;
}

.cancel-btn:hover {
  background-color: #444;
}
/* زر الانتظار*/
.Waiting-btn {
  background-color: #ffa500;
  color: white;
}

.Waiting-btn:hover {
  background-color: #ffa50085;
}
#numberInput {
  width: 100%;
  padding: 10px;
  font-size: 16px;
  margin-bottom: 20px;
  border: 1px solid #ccc;
  border-radius: 5px;
  box-sizing: border-box;
}
    </style>

	<div id="customModal_1" class="modal">
	  <div class="modal-content">		
		<h2>تزويد الاموال</h2>
		<h3 id="h1_">2</h3>
		<h3 id="h2_">2</h3>
		<input id="numberInput" type="text" placeholder="Enter a number (e.g., 2.1)" onkeypress="return isNumberKey(event)" />
		<div class="buttons">		
		  <button class="accept-btn" onclick="buttonAction('OK')">موافق</button>
		  <button class="Waiting-btn" onclick="buttonAction('Waiting')">منتظار</button>
		  <button class="reject-btn" onclick="buttonAction('NO')">رفض</button>
		  <button class="cancel-btn" onclick="closeModal()">إلغاء</button>
		</div>
	  </div>
  </div>
  <script>
   var id_=0;
	document.getElementById("customModal_1").style.display = "none";
    function openModal(id,payeer_id,money_value) {
		id_ = id;
		document.getElementById("customModal_1").style.display = "flex";
		document.getElementById("h1_").textContent = payeer_id;
		document.getElementById("h2_").textContent = money_value;
	}
	
	function closeModal() {
		document.getElementById("customModal_1").style.display = "none";
	}
	function buttonAction(action) {
    const inputElement = document.getElementById("numberInput");
    const inputValue = parseFloat(inputElement.value);

    const data = {
        id: id_,
        action: action,
        val: inputValue
    };

    fetch('', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams(data)
    })
    .then(response => response.text())
    .then(responseText => {
        console.log(responseText); // للعرض فقط - تقدر تشيله
        closeModal();
        window.location.reload(true); // إعادة تحميل الصفحة بعد نجاح العملية
    })
    .catch(error => {
        console.error('خطأ:', error);
    });
}

		
		
	
  </script>
    <!-- الأزرار -->
    <div class="buttons-container">
        <button class="btn pending " onclick="window.location.href = '?depositRequests=pending';">⌛ طلبات الايداع المنتظره</button>
        <button class="btn approved" onclick="window.location.href = '?depositRequests=ok';">✅ الطلبات الموافق عليها</button>
        <button class="btn rejected" onclick="window.location.href = '?depositRequests=no';">❌ الطلبات المرفوضة</button>
    </div>
	
	
	<?php
if (!empty($_SESSION['form_message'])) {
    echo $_SESSION['form_message'];
	
	if (!empty($_SESSION['form_message_ok'])) {
		unset($_SESSION['form_message']);
		unset($_SESSION['form_message_ok']);
	}else{$_SESSION['form_message_ok'] ='ok';}
}
?>
<?php  if ($_GET['depositRequests'] == 'no'){?>	

<style>
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    text-align: center;
    padding: 20px;
}

.container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 15px;
    padding: 10px;
}

.card {
    background: #f8d7da;
    border-radius: 10px;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
    padding: 15px;
    text-align: left;
    transition: transform 0.3s ease-in-out;
    position: relative;
}



.card p {
    margin: 8px 0;
    font-size: 16px;
    line-height: 1.5;
}

.card h3 {
    margin: 5px 0;
    color: #007bff;
}

.status-ok {
    color: green;
    font-weight: bold;
    cursor: pointer;
}

/* زر الحذف في أعلى يمين البطاقة */
.delete-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    background-color: red;
    color: white;
    text-align: center;
    padding: 5px 10px;
    border-radius: 5px;
    text-decoration: none;
    font-weight: bold;
    font-size: 14px;
}

.delete-btn:hover {
    background-color: darkred;
}

/* زر التعديل يبقى في مكانه أسفل البطاقة */
.edit-btn {
    display: block;
    background-color: #007bff;
    color: white;
    text-align: center;
    padding: 8px;
    margin-top: 10px;
    border-radius: 5px;
    text-decoration: none;
    font-weight: bold;
}

.edit-btn:hover {
    background-color: #0056b3;
}

/* العناصر الجديدة: "تم دفع" و "تم سحب" */
.payment-info {
    display: flex;
    justify-content: space-between;
    background-color: #f8f9fa;
    padding: 5px;
    border-radius: 5px;
    margin-top: 10px;
    font-weight: bold;
    font-size: 16px; /* تصغير الخط */
}

.payment-info .paid {
    color: #007bff; /* لون أزرق */
}

.payment-info .withdrawn {
    color: #dc3545; /* لون أحمر */
}

</style>
	<div class="content_title" id="'"> طلبات الايداع المرفوضه</div>
	
		<div class="container">
		<?php

			$query = "SELECT * FROM payments WHERE payment_status = 'no' ORDER BY created_at DESC";
			$result = mysqli_query($conn, $query);
				while ($row = mysqli_fetch_assoc($result)) {
				 $sql_check = "SELECT * FROM users WHERE user_id = ? ";
				$stmt_check = $conn->prepare($sql_check);
				$stmt_check->bind_param("i",$row['user_id']);
				$stmt_check->execute();
				$result0 = $stmt_check->get_result();
				if ($result0->num_rows > 0) {
				$row0 = $result0->fetch_assoc();  
				?>
			<div class="card">
				<a class="delete-btn" href="?depositRequests=no&delete_id=<?php echo $row['id']; ?>">🗑 حذف</a>
				<h3>
					ID: <?php echo $row['id']; ?>
				</h3>
				<h3>
					USER ID: <?php echo $row['user_id']; ?>
				</h3>
				<h3>
					username: <?php echo $row0['username']; ?>
				</h3>
				<p><strong>Wallet Name: </strong><?php echo $row['wallets_name']; ?></p>
				<p><strong>Wallet Value: </strong><?php echo $row['wallets_value']; ?></p>
				<p><strong>Amount: </strong><?php echo number_format($row['money_value'], 2); ?> RUB</p>
				<p><strong>Status: </strong>
					<span class="status-ok" onclick="openModal(<?php echo $row['id']; ?>,'<?php echo $row['wallets_name']; ?>','<?php echo $row['money_value']; ?>');">
						<?php echo $row['payment_status']; ?>
					</span>
				</p>
				<p><strong>Time: </strong><?php echo $row['created_at']; ?></p>
				<div class="payment-info">
					<span class="paid">تم دفع: <?php echo number_format($row0['payments_money'], 3, '.', '');?></span>
					<span class="withdrawn">تم سحب: <?php echo number_format($row0['withdrawals_money'], 3, '.', '');?></span>
				</div>

				<a class="edit-btn" onclick="openModal(<?php echo $row['id']; ?>,'<?php echo $row['user_id']; ?>','<?php echo $row['money_value']; ?>');">✏️ تعديل</a>
			</div>
		<?php
		}}
		?>
		</div>

		
<?php }elseif ($_GET['depositRequests'] == 'ok'){ ?>
<style>
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    text-align: center;
    padding: 20px;
}

.container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 15px;
    padding: 10px;
}

.card {
    background: #d4edda;
    border-radius: 10px;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
    padding: 15px;
    text-align: left;
    transition: transform 0.3s ease-in-out;
    position: relative;
}



.card p {
    margin: 8px 0;
    font-size: 16px;
    line-height: 1.5;
}

.card h3 {
    margin: 5px 0;
    color: #007bff;
}

.status-ok {
    color: green;
    font-weight: bold;
    cursor: pointer;
}

/* زر الحذف في أعلى يمين البطاقة */
.delete-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    background-color: red;
    color: white;
    text-align: center;
    padding: 5px 10px;
    border-radius: 5px;
    text-decoration: none;
    font-weight: bold;
    font-size: 14px;
}

.delete-btn:hover {
    background-color: darkred;
}

/* زر التعديل يبقى في مكانه أسفل البطاقة */
.edit-btn {
    display: block;
    background-color: #007bff;
    color: white;
    text-align: center;
    padding: 8px;
    margin-top: 10px;
    border-radius: 5px;
    text-decoration: none;
    font-weight: bold;
}

.edit-btn:hover {
    background-color: #0056b3;
}

/* العناصر الجديدة: "تم دفع" و "تم سحب" */
.payment-info {
    display: flex;
    justify-content: space-between;
    background-color: #f8f9fa;
    padding: 5px;
    border-radius: 5px;
    margin-top: 10px;
    font-weight: bold;
    font-size: 16px; /* تصغير الخط */
}

.payment-info .paid {
    color: #007bff; /* لون أزرق */
}

.payment-info .withdrawn {
    color: #dc3545; /* لون أحمر */
}

</style>
	<div class="content_title" id="'">طلبات الايداع الموافق عليها</div>
	
		<div class="container">
		<?php

			$query = "SELECT * FROM payments WHERE payment_status = 'ok' ORDER BY created_at DESC";
			$result = mysqli_query($conn, $query);
				while ($row = mysqli_fetch_assoc($result)) {
				 $sql_check = "SELECT * FROM users WHERE user_id = ? ";
				$stmt_check = $conn->prepare($sql_check);
				$stmt_check->bind_param("i",$row['user_id']);
				$stmt_check->execute();
				$result0 = $stmt_check->get_result();
				if ($result0->num_rows > 0) {
				$row0 = $result0->fetch_assoc();  
				?>
			<div class="card">
				<a class="delete-btn" href="?depositRequests=ok&delete_id=<?php echo $row['id']; ?>">🗑 حذف</a>
				<h3>
					ID: <?php echo $row['id']; ?>
				</h3>
				<h3>
					USER ID: <?php echo $row['user_id']; ?>
				</h3>
				<h3>
					username: <?php echo $row0['username']; ?>
				</h3>
				<p><strong>Wallet Name: </strong><?php echo $row['wallets_name']; ?></p>
				<p><strong>Wallet Value: </strong><?php echo $row['wallets_value']; ?></p>
				<p><strong>Amount: </strong><?php echo number_format($row['money_value'], 2); ?> RUB</p>
				<p><strong>Status: </strong>
					<span class="status-ok" onclick="openModal(<?php echo $row['id']; ?>,'<?php echo $row['wallets_name']; ?>','<?php echo $row['money_value']; ?>');">
						<?php echo $row['payment_status']; ?>
					</span>
				</p>
				<p><strong>Time: </strong><?php echo $row['created_at']; ?></p>
				<div class="payment-info">
					<span class="paid">تم دفع: <?php echo number_format($row0['payments_money'], 3, '.', '');?></span>
					<span class="withdrawn">تم سحب: <?php echo number_format($row0['withdrawals_money'], 3, '.', '');?></span>
				</div>

				<a class="edit-btn" onclick="openModal(<?php echo $row['id']; ?>,'<?php echo $row['user_id']; ?>','<?php echo $row['money_value']; ?>');">✏️ تعديل</a>
			</div>
		<?php
		}}
		?>
		</div>

<?php }else{ ?>	
<style>
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    text-align: center;
    padding: 20px;
}

.container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 15px;
    padding: 10px;
}

.card {
    background: #efc372c4;
    border-radius: 10px;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
    padding: 15px;
    text-align: left;
    transition: transform 0.3s ease-in-out;
    position: relative;
}



.card p {
    margin: 8px 0;
    font-size: 16px;
    line-height: 1.5;
}

.card h3 {
    margin: 5px 0;
    color: #007bff;
}

.status-ok {
    color: green;
    font-weight: bold;
    cursor: pointer;
}

/* زر الحذف في أعلى يمين البطاقة */
.delete-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    background-color: red;
    color: white;
    text-align: center;
    padding: 5px 10px;
    border-radius: 5px;
    text-decoration: none;
    font-weight: bold;
    font-size: 14px;
}

.delete-btn:hover {
    background-color: darkred;
}

/* زر التعديل يبقى في مكانه أسفل البطاقة */
.edit-btn {
    display: block;
    background-color: #007bff;
    color: white;
    text-align: center;
    padding: 8px;
    margin-top: 10px;
    border-radius: 5px;
    text-decoration: none;
    font-weight: bold;
}

.edit-btn:hover {
    background-color: #0056b3;
}

/* العناصر الجديدة: "تم دفع" و "تم سحب" */
.payment-info {
    display: flex;
    justify-content: space-between;
    background-color: #f8f9fa;
    padding: 5px;
    border-radius: 5px;
    margin-top: 10px;
    font-weight: bold;
    font-size: 16px; /* تصغير الخط */
}

.payment-info .paid {
    color: #007bff; /* لون أزرق */
}

.payment-info .withdrawn {
    color: #dc3545; /* لون أحمر */
}

</style>	
	<div class="content-title"> طلبات قيد الانتظار</div>
		<div class="container">
		<?php

			$query = "SELECT * FROM payments WHERE payment_status = 'pending' ORDER BY created_at DESC";
			$result = mysqli_query($conn, $query);
				while ($row = mysqli_fetch_assoc($result)) {
				 $sql_check = "SELECT * FROM users WHERE user_id = ? ";
				$stmt_check = $conn->prepare($sql_check);
				$stmt_check->bind_param("i",$row['user_id']);
				$stmt_check->execute();
				$result0 = $stmt_check->get_result();
				if ($result0->num_rows > 0) {
				$row0 = $result0->fetch_assoc();  
				?>
			<div class="card">
				<a class="delete-btn" href="?depositRequests=Waiting&delete_id=<?php echo $row['id']; ?>">🗑 حذف</a>
				<h3>
					ID: <?php echo $row['id']; ?>
				</h3>
				<h3>
					USER ID: <?php echo $row['user_id']; ?>
				</h3>
				<h3>
					username: <?php echo $row0['username']; ?>
				</h3>
				<p><strong>Wallet Name: </strong><?php echo $row['wallets_name']; ?></p>
				<p><strong>Wallet Value: </strong><?php echo $row['wallets_value']; ?></p>
				<p><strong>Amount: </strong><?php echo number_format($row['money_value'], 2); ?> RUB</p>
				<p><strong>Status: </strong>
					<span class="status-ok" onclick="openModal(<?php echo $row['id']; ?>,'<?php echo $row['wallets_name']; ?>','<?php echo $row['money_value']; ?>');">
						<?php echo $row['payment_status']; ?>
					</span>
				</p>
				<p><strong>Time: </strong><?php echo $row['created_at']; ?></p>
				<div class="payment-info">
					<span class="paid">تم دفع: <?php echo number_format($row0['payments_money'], 3, '.', '');?></span>
					<span class="withdrawn">تم سحب: <?php echo number_format($row0['withdrawals_money'], 3, '.', '');?></span>
				</div>

				<a class="edit-btn" onclick="openModal(<?php echo $row['id']; ?>,'<?php echo $row['user_id']; ?>','<?php echo $row['money_value']; ?>');">✏️ تعديل</a>
			</div>
		<?php
		}}
		?>
		</div>		
		<?php } ?>