<?php


if (isset($_GET['users'])) {

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($_POST['action'] === 'ban' && isset($_POST['id'])) {
            $blocked = $_POST['banned'] == 1 ? 0 : 1;
            $stmt = $conn->prepare("UPDATE users SET blocked = ? WHERE user_id = ?");
            $stmt->bind_param("ii", $blocked, $_POST['id']);
            $stmt->execute();
            exit('done');
        }

        if ($_POST['action'] === 'update') {
            $stmt = $conn->prepare("UPDATE users SET email = ?, username = ?, password = ? WHERE user_id = ?");
            $stmt->bind_param("sssi", $_POST['email'], $_POST['username'], $_POST['password'], $_POST['id']);
            $stmt->execute();

            $stmt2 = $conn->prepare("UPDATE user_wallets SET payeer_wallet = ? WHERE user_id = ?");
            $stmt2->bind_param("si", $_POST['payeer_wallet'], $_POST['id']);
            $stmt2->execute();

            $stmt3 = $conn->prepare("UPDATE user_money SET available_to_withdraw = ?, advertising_balance = ? WHERE user_id = ?");
            $stmt3->bind_param("ddi", $_POST['withdraw'], $_POST['ad_balance'], $_POST['id']);
            $stmt3->execute();

            exit('updated');
        }
    }

    if ($_GET['users'] == 'dalet' && isset($_GET['id'])) {
               if (filter_var($_GET['id'], FILTER_VALIDATE_INT) !== false) {
            $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->bind_param("i", $_GET['id']);
            $stmt->execute();
			$stmt = $conn->prepare("DELETE FROM user_activity WHERE user_id = ?");
            $stmt->bind_param("i", $_GET['id']);
            $stmt->execute();
			$stmt = $conn->prepare("DELETE FROM referrals WHERE referrer_id = ?");
            $stmt->bind_param("i", $_GET['id']);
            $stmt->execute();
			$stmt = $conn->prepare("DELETE FROM referrals WHERE referred_id = ?");
            $stmt->bind_param("i", $_GET['id']);
            $stmt->execute();
			$stmt = $conn->prepare("DELETE FROM withdrawals WHERE user_id = ?");
            $stmt->bind_param("i", $_GET['id']);
            $stmt->execute();
			$stmt = $conn->prepare("DELETE FROM payments WHERE user_id = ?");
            $stmt->bind_param("i", $_GET['id']);
            $stmt->execute();
			$stmt = $conn->prepare("DELETE FROM user_money WHERE user_id = ?");
            $stmt->bind_param("i", $_GET['id']);
            $stmt->execute();
			$stmt = $conn->prepare("DELETE FROM user_subscriptions WHERE user_id = ?");
            $stmt->bind_param("i", $_GET['id']);
            $stmt->execute();
			$stmt = $conn->prepare("DELETE FROM user_mining WHERE user_id = ?");
            $stmt->bind_param("i", $_GET['id']);
            $stmt->execute();
			$stmt = $conn->prepare("DELETE FROM user_weekly_subscriptions WHERE user_id = ?");
            $stmt->bind_param("i", $_GET['id']);
            $stmt->execute();
			$stmt = $conn->prepare("DELETE FROM daily_gifts WHERE user_id = ?");
            $stmt->bind_param("i", $_GET['id']);
            $stmt->execute();
			$stmt = $conn->prepare("DELETE FROM bounty_submissions WHERE user_id = ?");
            $stmt->bind_param("i", $_GET['id']);
            $stmt->execute();
			$stmt = $conn->prepare("DELETE FROM ads_link WHERE user_id = ?");
            $stmt->bind_param("i", $_GET['id']);
            $stmt->execute();
			$stmt = $conn->prepare("DELETE FROM ads_views WHERE user_id = ?");
            $stmt->bind_param("i", $_GET['id']);
            $stmt->execute();
			$stmt = $conn->prepare("DELETE FROM ads_text WHERE user_id = ?");
            $stmt->bind_param("i", $_GET['id']);
            $stmt->execute();
			$stmt = $conn->prepare("DELETE FROM ads_banner WHERE user_id = ?");
            $stmt->bind_param("i", $_GET['id']);
            $stmt->execute();
			$stmt = $conn->prepare("DELETE FROM user_wallets WHERE user_id = ?");
            $stmt->bind_param("i", $_GET['id']);
            $stmt->execute();
            echo '<div class="msg">User deleted successfully</div><script>setTimeout(()=>location.href="?users", 100);</script>';
        }
    }

	
	
	?>

<style>
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
.container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 20px;
	padding:2px;
}
.card {
    background: #efc372c4;
    border-radius: 15px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    padding: 25px;
    text-align: left;
    position: relative;
    transition: transform 0.3s ease;
}.status-ok {
    color: green;
    font-weight: bold;
    cursor: pointer;
}

.card p {
    margin: 8px 0;
    font-size: 15px;
}
input[type="text"], input[type="number"] {
    width: 100%;
    padding: 6px 8px;
    margin: 4px 0;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 14px;
}
.delete-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    background-color: crimson;
    color: #fff;
    padding: 6px 12px;
    border-radius: 8px;
    text-decoration: none;
}
.buttons-container {
    margin-top: 15px;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}
.btn {
    padding: 8px 14px;
    border: none;
    border-radius: 8px;
    color: #fff;
    font-size: 14px;
    cursor: pointer;
}
.btn-edit { background-color: #3498db; }
.btn-ban { background-color: #27ae60; }
.btn-unban { background-color: #e74c3c; }

.modal-overlay {
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex; justify-content: center; align-items: center;
    z-index: 9999;
}
.modal-box {
    background: #fff;
    padding: 20px 30px;
    border-radius: 10px;
    text-align: center;
    max-width: 300px;
    box-shadow: 0 0 15px rgba(0,0,0,0.3);
}
.modal-buttons {
    margin-top: 15px;
    display: flex; justify-content: center; gap: 10px;
}
.modal-buttons button {
    padding: 6px 15px;
    border: none;
    border-radius: 5px;
    font-weight: bold;
    cursor: pointer;
}
.modal-buttons .yes {
    background-color: #e74c3c;
    color: white;
}
.modal-buttons .no {
    background-color: #bdc3c7;
}
.modal-buttons button:hover {
    opacity: 0.9;
}.card h3 {
    margin: 5px 0;
    color: #007bff;
}
.btn-open {
    background-color: #8e44ad; /* بنفسجي */
    text-decoration: none;
    display: inline-block;
    text-align: center;
    line-height: normal;
}
.btn-open:hover {
    opacity: 0.85;
}

</style>


<?php

$sql = "SELECT 
            u.*, 
            ua.last_seen, 
            ua.is_online, 
            um.available_to_withdraw, 
            um.advertising_balance,
            uw.payeer_wallet,
            (SELECT COUNT(*) FROM referrals WHERE referrer_id = u.user_id) AS total_referrals,
            r.referrer_id
        FROM users u
        LEFT JOIN user_activity ua ON ua.user_id = u.user_id
        LEFT JOIN user_money um ON um.user_id = u.user_id
        LEFT JOIN user_wallets uw ON uw.user_id = u.user_id
        LEFT JOIN referrals r ON r.referred_id = u.user_id
		
        ORDER BY u.user_id DESC";


$result = $conn->query($sql);
?>

<div class="container">
<?php while ($row = $result->fetch_assoc()):
	
	?>
   <?php
$bg = '#efc372c4'; // اللون الطبيعي

if ($row['blocked']) {
    $bg = '#19181ca1'; // بلوك
} elseif (!$row['email_verified']) {
    $bg = '#ffcccc'; // مش مفعل الايميل
} elseif ($row['is_online']) {
    $bg = '#4f9f4f66'; // اونلاين
}
?>
<div class="card" style="background-color: <?= $bg ?>;" id="user-<?= $row['user_id'] ?>">

        
        <!-- زر الحذف مخفي وبتأكيد -->
       <a class="delete-btn" 
		   href="javascript:void(0);" 
		   onclick="showDeleteModal(<?= $row['user_id'] ?>)" 
		   title="Delete User">🗑</a>
		<h3>ID: <?php echo $row['user_id']; ?> </h3>

        <p><strong>Email:</strong> <span onclick="copyText(this)" class="status-ok"><span data-field="email"><?= $row['email'] ?></span></span></p>
        <p><strong>Username:</strong> <span onclick="copyText(this)" class="status-ok"><span data-field="username"><?= $row['username'] ?></span></span></p>
        <p><strong>Password:</strong> <span onclick="copyText(this)" class="status-ok"><span data-field="password"><?= $row['password'] ?></span></span></p>
        <p><strong>Payeer:</strong> <span onclick="copyText(this)" class="status-ok"><span data-field="payeer_wallet"><?= $row['payeer_wallet'] ?></span></span></p>
        <p><strong>Withdrawable Balance:</strong> <span data-field="withdraw"><?= $row['available_to_withdraw'] ?? 0 ?></span> $</p>
        <p><strong>Ad Balance:</strong> <span data-field="ad_balance"><?= $row['advertising_balance'] ?? 0 ?></span> $</p>
        <p><strong>Referrals:</strong> <?= $row['total_referrals'] ?></p>
        <p><strong>Last Seen:</strong> <?= $row['last_seen'] ?></p>
        <p><strong>Status:</strong> <?= $row['is_online'] ? '<span class="status-ok" >✅ Online </span>' : '❌ Offline' ?></p>
        <p><strong>Signup Time:</strong> <?= $row['record_time'] ?></p>
		<p><strong>User IP:</strong> <?= $row['user_IP'] ?></p>
		<p><strong>http_referrer : </strong><span onclick="copyText(this)" class="status-ok" ><?php echo htmlspecialchars($row['http_referrer']?? "none" ); ?></span>  /ref=<?php echo $row['referrer_id']?? "0" ; ?></p>
        <div class="payment-info">
					<span class="paid">تم دفع: <?php echo number_format($row['payments_money'], 3, '.', '');?></span>
					<span class="withdrawn">تم سحب: <?php echo number_format($row['withdrawals_money'], 3, '.', '');?></span>
		</div>
		
		<div class="buttons-container">
            <button class="btn <?= $row['blocked'] ? 'btn-unban' : 'btn-ban' ?>" onclick="banUser(<?= $row['user_id'] ?>, <?= $row['blocked'] ?>)">
                <?= $row['blocked'] ? 'Unban User' : 'Ban User' ?>
            </button>
            <button class="btn btn-edit" onclick="enableEdit(<?= $row['user_id'] ?>)">✏️ Edit</button>
			    <a href="/look/?users&open=<?= $row['user_id'] ?>" class="btn btn-open" target="_blank">🌐 Open Account</a>

        </div>
    </div>
<?php endwhile; ?>
</div>
<div id="deleteModal" class="modal-overlay" style="display: none;">
  <div class="modal-box">
    <p>⚠️ هل أنت متأكد أنك تريد حذف هذا المستخدم؟</p>
    <div class="modal-buttons">
      <button onclick="confirmDelete()" class="yes">نعم</button>
      <button onclick="closeDeleteModal()" class="no">لا</button>
    </div>
  </div>
</div>


<script>
function banUser(id, current) {
    fetch('', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({ action: 'ban', id, banned: current })
    }).then(() => location.reload());
}

function enableEdit(id) {
    const card = document.getElementById('user-' + id);
    ['email', 'username', 'password', 'payeer_wallet', 'withdraw', 'ad_balance'].forEach(field => {
        const span = card.querySelector(`[data-field="${field}"]`);
        const val = span.textContent.trim();
        const type = field === 'withdraw' || field === 'ad_balance' ? 'number' : 'text';
        span.innerHTML = `<input type="${type}" name="${field}" value="${val}" step="0.01">`;
    });

    card.querySelector('.buttons-container').innerHTML = `
        <button class="btn btn-edit" onclick="saveUser(${id})">💾 Save</button>
    `;
}

function saveUser(id) {
    const card = document.getElementById('user-' + id);
    const data = {
        action: 'update',
        id: id,
        email: card.querySelector('[name="email"]').value,
        username: card.querySelector('[name="username"]').value,
        password: card.querySelector('[name="password"]').value,
        payeer_wallet: card.querySelector('[name="payeer_wallet"]').value,
        withdraw: parseFloat(card.querySelector('[name="withdraw"]').value),
        ad_balance: parseFloat(card.querySelector('[name="ad_balance"]').value)
    };

    fetch('', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams(data)
    }).then(() => location.reload());
}

let deleteUserId = null;

function showDeleteModal(userId) {
    deleteUserId = userId;
    document.getElementById("deleteModal").style.display = "flex";
}

function closeDeleteModal() {
    deleteUserId = null;
    document.getElementById("deleteModal").style.display = "none";
}

function confirmDelete() {
    if (deleteUserId !== null) {
        window.location.href = "?users=dalet&id=" + deleteUserId;
    }
}

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
<?php } ?>
