<?php
function addWithdrawalMessage($conn, $userId, $amount) {
    $title = "💸 Withdrawal Successful! 💸";

    $message = "
    <div class='modal fade' id='withdrawalModal' tabindex='-1' aria-labelledby='withdrawalModalLabel' aria-hidden='true'>
      <div class='modal-dialog modal-dialog-centered'>
        <div class='modal-content withdrawal-modal-content'>
          <div class='modal-header border-0'>
            <h5 class='modal-title w-100 text-center' id='withdrawalModalLabel'>
              <i class='bi bi-wallet2'></i> {$title} <i class='bi bi-arrow-down-circle-fill'></i>
            </h5>
            <button type='button' class='btn-close position-absolute end-0 top-0 m-3' data-bs-dismiss='modal' aria-label='Close'></button>
          </div>
          <div class='modal-body'>
            <p class='lead'>
              <i class='bi bi-cash-stack'></i> Your withdrawal of 
              <strong class='text-warning'>{$amount} RUB</strong> 
              has been <span class='text-info fw-bold'>successfully processed</span> ✅
            </p>
            <p class='mt-3'>
              The amount has been sent to your registered account.<br>
              Thank you for using our platform. 💜
            </p>
          </div>
          <div class='modal-footer border-0 justify-content-center'>
            <button type='button' class='btn btn-glow' data-bs-dismiss='modal'>
              <i class='bi bi-check-circle-fill'></i>  OK, Thanks
            </button>
          </div>
        </div>
      </div>
    </div>

    <script>
      window.addEventListener('DOMContentLoaded', () => {
        const modal = new bootstrap.Modal(document.getElementById('withdrawalModal'));
        modal.show();
      });
    </script>

    <style>
      .withdrawal-modal-content {
        background: linear-gradient(17deg,#1a0d32 0%, #50437a 100%);
        border-radius: 18px;
        padding: 25px;
        color: #ffffff;
        text-align: center;
        box-shadow: 0 12px 35px rgba(0,0,0,0.25);
        animation: popUp 0.4s ease-in-out;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      }
      .withdrawal-modal-content h5 {
        font-size: 26px;
        font-weight: bold;
        color: #ffffff;
      }
      .withdrawal-modal-content p {
        font-size: 17px;
        margin: 12px 0;
        color: #ffffff;
      }
      .btn-glow {
        background: linear-gradient(90deg, #b39ddb, #9575cd);
        border: none;
        color: #fff;
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
    if (isset($_POST['id'], $_POST['action'])) {
        $id = intval($_POST['id']);
        $action = $_POST['action'];
       

        // جلب بيانات العملية
        $query = "SELECT * FROM withdrawals WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $payment = $result->fetch_assoc();
            $user_id = $payment['user_id'];
			$val =  $payment['money_value'];
			$status = $payment['withdrawal_status'];
            if ($action === 'OK') {
                $update = $conn->prepare("UPDATE withdrawals SET withdrawal_status = 'ok' WHERE id = ?");
                $update->bind_param("i", $id);
                $update->execute();
				if($status != 'ok' ){
					$update_user = $conn->prepare("UPDATE users SET withdrawals_money = withdrawals_money + ? WHERE user_id = ?");
					$update_user->bind_param("di",$val, $user_id);
					$update_user->execute();
					addWithdrawalMessage($conn, $user_id, $val);
				}
				$_SESSION['form_message'] = "<div class='alert alert-success'> ❌ ✅ تمت الموافقة على العملية.</div>";

            } elseif ($action === 'NO') {
                $update = $conn->prepare("UPDATE withdrawals SET withdrawal_status = 'no' WHERE id = ?");
                $update->bind_param("i", $id);
                $update->execute();
				if($status == 'ok' ){
				$update_user = $conn->prepare("UPDATE users SET withdrawals_money = withdrawals_money - ? WHERE user_id = ?");
                $update_user->bind_param("di",$val, $user_id);
                $update_user->execute();
				$delete_sql = "DELETE FROM message_admin WHERE user_id = ?";
				$del_stmt = $conn->prepare($delete_sql);
				$del_stmt->bind_param("i", $user_id);
				$del_stmt->execute();
				}	

				 $_SESSION['form_message'] = "<div class='alert alert-success'>❌ تم رفض العملية</div>";
            } elseif ($action === 'Waiting') {
                $update = $conn->prepare("UPDATE withdrawals SET withdrawal_status = 'pending' WHERE id = ?");
                $update->bind_param("i", $id);
                $update->execute();
				
				if( $status == 'ok' ){
					$update_user = $conn->prepare("UPDATE users SET withdrawals_money = withdrawals_money - ? WHERE user_id = ?");
					$update_user->bind_param("di",$val, $user_id);
					$update_user->execute();
					$delete_sql = "DELETE FROM message_admin WHERE user_id = ?";
					$del_stmt = $conn->prepare($delete_sql);
					$del_stmt->bind_param("i", $user_id);
					$del_stmt->execute();
				}	
            
				$_SESSION['form_message'] = "<div class='alert alert-success'> ⏳ تم إرجاع الطلب إلى قيد الانتظار</div>";

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


if (isset($_GET['withdrawRequests']) && isset($_GET['delete_id']) ){
			if (filter_var($_GET['delete_id'], FILTER_VALIDATE_INT) !== false) {
			$stmt = $conn->prepare("DELETE FROM withdrawals WHERE id = ? "); /////////////////				
			$stmt->bind_param("i",$_GET['delete_id']);
			$stmt->execute();
			}
			?>
<div class="msg">تم الحذف بنجاح</div><script type="text/javascript">setTimeout(function() {window.location.href ="?withdrawRequests";}, 1000);</script>
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
    <h2>هل ستدفع</h2>
    <h3 id="h1_">2</h3>
    <h3 id="h2_"  onclick="copyText(this)">2</h3>

    <!-- input read-only + onclick للنسخ -->
    <h3 id="wallet_value"  onclick="copyText(this)" >3</h3>

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

function openModal(id,wallets_name,wallet_value,money_value) { 
	id_ = id;
	document.getElementById("customModal_1").style.display = "flex";
	document.getElementById("h1_").textContent = wallets_name;
	document.getElementById("h2_").textContent = money_value;
	document.getElementById("wallet_value").textContent = wallet_value;
}

function closeModal() {
	document.getElementById("customModal_1").style.display = "none";
}

function buttonAction(action) {


    const data = {
        id: id_,
        action: action
      
    };

    fetch('', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams(data)
    })
    .then(response => response.text())
    .then(responseText => {
        console.log(responseText); 
        closeModal();
        window.location.reload(true);
    })
    .catch(error => {
        console.error('خطأ:', error);
    });
}

// دالة نسخ المحفظة

function copyText(element) {
    // استخراج النص من العنصر
    var text = element.innerText || element.textContent;

    // إنشاء عنصر إدخال مؤقت لنسخ النص
    var tempInput = document.createElement("textarea");
    tempInput.value = text;
    document.body.appendChild(tempInput);

    // تحديد النص ونسخه إلى الحافظة
    tempInput.select();
    document.execCommand("copy");

    // إزالة العنصر المؤقت
    document.body.removeChild(tempInput);

    // إنشاء إشعار مؤقت
    var notification = document.createElement("div");
    notification.innerText = "تم النسخ!  "+text;
    notification.style.position = "fixed";
    notification.style.bottom = "20px";
    notification.style.left = "50%";
    notification.style.transform = "translateX(-50%)";
    notification.style.background = "rgba(0, 0, 0, 0.7)";
    notification.style.color = "#fff";
    notification.style.padding = "10px 20px";
    notification.style.borderRadius = "5px";
    notification.style.fontSize = "16px";
    notification.style.zIndex = "9999";
    notification.style.transition = "opacity 0.5s";
    
    document.body.appendChild(notification);

    // إخفاء الإشعار بعد 2 ثانية
    setTimeout(function () {
        notification.style.opacity = "0";
        setTimeout(() => document.body.removeChild(notification), 500);
    }, 2000);
}
</script>

    <!-- الأزرار -->
    <div class="buttons-container">
        <button class="btn pending " onclick="window.location.href = '?withdrawRequests=pending';">⌛ طلبات السحب المنتظره</button>
        <button class="btn approved" onclick="window.location.href = '?withdrawRequests=ok';">✅ الطلبات الموافق عليها</button>
        <button class="btn rejected" onclick="window.location.href = '?withdrawRequests=no';">❌ الطلبات المرفوضة</button>
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
<?php  if ($_GET['withdrawRequests'] == 'no'){?>	

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
	<div class="content_title" id="'">طلبات السحب المرفوضة</div>
	
		<div class="container">
		<?php

$query = "SELECT * FROM withdrawals WHERE withdrawal_status = 'no' ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
while ($row = mysqli_fetch_assoc($result)) {
    // بيانات المستخدم
    $sql_check = "SELECT * FROM users WHERE user_id = ? ";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("i", $row['user_id']);
    $stmt_check->execute();
    $result0 = $stmt_check->get_result();
    if ($result0->num_rows > 0) {
        $row0 = $result0->fetch_assoc();

        // بيانات المحافظ
        $sql_w = "SELECT * FROM user_wallets WHERE user_id = ? ";
        $stmt_check0 = $conn->prepare($sql_w);
        $stmt_check0->bind_param("i", $row['user_id']);
        $stmt_check0->execute();
        $result01 = $stmt_check0->get_result();
        if ($result01->num_rows > 0) {
            $row01 = $result01->fetch_assoc();

            // هنا بجيب العمود الديناميكي
            $wallet_column = $row['wallets_name'] . "_wallet";
            $value__wallet = isset($row01[$wallet_column]) ? $row01[$wallet_column] : "غير موجود";

            ?>
            <div class="card">
               <a class="delete-btn" href="?withdrawRequests=Waiting&delete_id=<?php echo $row['id']; ?>">🗑 حذف</a>
                <h3>ID: <?php echo $row['id']; ?></h3>
                <h3>USER ID: <?php echo $row['user_id']; ?></h3>
                <p><strong>Wallet Name: </strong><?php echo $row['wallets_name']; ?></p>
                <p><strong>Wallet Address: </strong><?php echo $value__wallet; ?></p>
                <p><strong>Amount: </strong><?php echo number_format($row['money_value'], 2); ?> RUB</p>
                <p><strong>Status: </strong>
                    <span class="status-ok" onclick="openModal(<?php echo $row['id']; ?>,'<?php echo $row['wallets_name']; ?>','<?php echo $value__wallet; ?>','<?php echo $row['money_value']; ?>');">
                        <?php echo $row['withdrawal_status']; ?>
                    </span>
                </p>
                <p><strong>Time: </strong><?php echo $row['created_at']; ?></p>
                <div class="payment-info">
                    <span class="paid">تم دفع: <?php echo number_format($row0['payments_money'], 3, '.', '');?></span>
                    <span class="withdrawn">تم سحب: <?php echo number_format($row0['withdrawals_money'], 3, '.', ''); ?></span>
                </div>
                <a class="edit-btn" onclick="openModal(<?php echo $row['id']; ?>,'<?php echo $row['wallets_name']; ?>','<?php echo $value__wallet; ?>','<?php echo $row['money_value']; ?>');">✏️ تعديل</a>
            </div>
            <?php
        }
    }
}

		?>
		</div>

		
<?php }elseif ($_GET['withdrawRequests'] == 'ok'){ ?>
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
	<div class="content_title" id="'">طلبات السحب الموفق عليها</div>
	
		<div class="container">
				<?php
$query = "SELECT * FROM withdrawals WHERE withdrawal_status = 'ok' ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
while ($row = mysqli_fetch_assoc($result)) {
    // بيانات المستخدم
    $sql_check = "SELECT * FROM users WHERE user_id = ? ";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("i", $row['user_id']);
    $stmt_check->execute();
    $result0 = $stmt_check->get_result();
    if ($result0->num_rows > 0) {
        $row0 = $result0->fetch_assoc();

        // بيانات المحافظ
        $sql_w = "SELECT * FROM user_wallets WHERE user_id = ? ";
        $stmt_check0 = $conn->prepare($sql_w);
        $stmt_check0->bind_param("i", $row['user_id']);
        $stmt_check0->execute();
        $result01 = $stmt_check0->get_result();
        if ($result01->num_rows > 0) {
            $row01 = $result01->fetch_assoc();

            // هنا بجيب العمود الديناميكي
            $wallet_column = $row['wallets_name'] . "_wallet";
            $value__wallet = isset($row01[$wallet_column]) ? $row01[$wallet_column] : "غير موجود";

            ?>
            <div class="card">
               <a class="delete-btn" href="?withdrawRequests=Waiting&delete_id=<?php echo $row['id']; ?>">🗑 حذف</a>
                <h3>ID: <?php echo $row['id']; ?></h3>
                <h3>USER ID: <?php echo $row['user_id']; ?></h3>
                <p><strong>Wallet Name: </strong><?php echo $row['wallets_name']; ?></p>
                <p><strong>Wallet Address: </strong><?php echo $value__wallet; ?></p>
                <p><strong>Amount: </strong><?php echo number_format($row['money_value'], 2); ?> RUB</p>
                <p><strong>Status: </strong>
                    <span class="status-ok" onclick="openModal(<?php echo $row['id']; ?>,'<?php echo $row['wallets_name']; ?>','<?php echo $value__wallet; ?>','<?php echo $row['money_value']; ?>');">
                        <?php echo $row['withdrawal_status']; ?>
                    </span>
                </p>
                <p><strong>Time: </strong><?php echo $row['created_at']; ?></p>
                <div class="payment-info">
                    <span class="paid">تم دفع: <?php echo number_format($row0['payments_money'], 3, '.', '');?></span>
                    <span class="withdrawn">تم سحب: <?php echo number_format($row0['withdrawals_money'], 3, '.', ''); ?></span>
                </div>
                <a class="edit-btn" onclick="openModal(<?php echo $row['id']; ?>,'<?php echo $row['wallets_name']; ?>','<?php echo $value__wallet; ?>','<?php echo $row['money_value']; ?>');">✏️ تعديل</a>
            </div>
            <?php
        }
    }
}

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
$query = "SELECT * FROM withdrawals WHERE withdrawal_status = 'pending' ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
while ($row = mysqli_fetch_assoc($result)) {
    // بيانات المستخدم
    $sql_check = "SELECT * FROM users WHERE user_id = ? ";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("i", $row['user_id']);
    $stmt_check->execute();
    $result0 = $stmt_check->get_result();
    if ($result0->num_rows > 0) {
        $row0 = $result0->fetch_assoc();

        // بيانات المحافظ
        $sql_w = "SELECT * FROM user_wallets WHERE user_id = ? ";
        $stmt_check0 = $conn->prepare($sql_w);
        $stmt_check0->bind_param("i", $row['user_id']);
        $stmt_check0->execute();
        $result01 = $stmt_check0->get_result();
        if ($result01->num_rows > 0) {
            $row01 = $result01->fetch_assoc();

            // هنا بجيب العمود الديناميكي
            $wallet_column = $row['wallets_name'] . "_wallet";
            $value__wallet = isset($row01[$wallet_column]) ? $row01[$wallet_column] : "غير موجود";

            ?>
            <div class="card">
               <a class="delete-btn" href="?withdrawRequests=Waiting&delete_id=<?php echo $row['id']; ?>">🗑 حذف</a>
                <h3>ID: <?php echo $row['id']; ?></h3>
                <h3>USER ID: <?php echo $row['user_id']; ?></h3>
                <p><strong>Wallet Name: </strong><?php echo $row['wallets_name']; ?></p>
                <p><strong>Wallet Address: </strong><?php echo $value__wallet; ?></p>
                <p><strong>Amount: </strong><?php echo number_format($row['money_value'], 2); ?> RUB</p>
                <p><strong>Status: </strong>
                    <span class="status-ok" onclick="openModal(<?php echo $row['id']; ?>,'<?php echo $row['wallets_name']; ?>','<?php echo $value__wallet; ?>','<?php echo $row['money_value']; ?>');">
                        <?php echo $row['withdrawal_status']; ?>
                    </span>
                </p>
                <p><strong>Time: </strong><?php echo $row['created_at']; ?></p>
                <div class="payment-info">
                    <span class="paid">تم دفع: <?php echo number_format($row0['payments_money'], 3, '.', '');?></span>
                    <span class="withdrawn">تم سحب: <?php echo number_format($row0['withdrawals_money'], 3, '.', ''); ?></span>
                </div>
                <a class="edit-btn" onclick="openModal(<?php echo $row['id']; ?>,'<?php echo $row['wallets_name']; ?>','<?php echo $value__wallet; ?>','<?php echo $row['money_value']; ?>');">✏️ تعديل</a>
            </div>
            <?php
        }
    }
}

		?>
		</div>		
		<?php } ?>