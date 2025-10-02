<?php 
//session_name('MY_SESSION');
session_start();
$user_admin ='moodlook';
$pass_admin ='475369mnm';
if(isset($_SESSION['user_admin'])&&  isset($_SESSION['pass_admin'])){ 
if( $_SESSION['user_admin'] == $user_admin &&  $_SESSION['pass_admin'] == $pass_admin){

if (isset($_GET['exit'])){// تسجيل الخروج
session_start();
session_destroy(); 
header("location: /look");
}else{ 
$server = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "green";
$conn = new mysqli($server, $db_user, $db_pass, $db_name);

$conn->set_charset("utf8mb4");
include('stert.php');
 	

	
		
}}}else{
include("login.php");
}

?>



<?php
#addGiftMessage($conn, 10802);
function addGiftMessage($conn, $userId) {
    $title = "🎁 A Special Gift for You! 🎁";
    $message = "     <div class='modal fade' id='giftModal' tabindex='-1' aria-labelledby='giftModalLabel' aria-hidden='true'>
      <div class='modal-dialog modal-dialog-centered'>
        <div class='modal-content gift-modal-content'>
          <div class='modal-header border-0'>
            <h5 class='modal-title w-100 text-center' id='giftModalLabel'>
              <i class='bi bi-gift-fill'></i> {$title} <i class='bi bi-stars'></i>
            </h5>
            <button type='button' class='btn-close position-absolute end-0 top-0 m-3' data-bs-dismiss='modal' aria-label='Close'></button>
          </div>
          <div class='modal-body'>
            <p class='lead fw-bold text-warning'>
              🎉 Congratulations! To celebrate your registration and our milestone of reaching 10,000 users 🎉
            </p>
            <p class='mt-3 fs-5'>
              You have received a <strong class='text-success'>Free Mining Package</strong>
               worth <span class='fw-bold text-info'>50 RUB</span> as a special gift from us ✨
            </p>
            <p class='mt-3'>
              Thank you for being part of our journey. We wish you amazing profits ahead 🚀
            </p>
          </div>
          <div class='modal-footer border-0 justify-content-center'>
            <button type='button' class='btn btn-glow' data-bs-dismiss='modal'>
              <i class='bi bi-check-circle-fill'></i>  Enjoy My Gift
            </button>
          </div>
        </div>
      </div>
    </div>

    <script>
      window.addEventListener('DOMContentLoaded', () => {
        const modal = new bootstrap.Modal(document.getElementById('giftModal'));
        modal.show();
      });
    </script>

    <style>
      .gift-modal-content {
        background: linear-gradient(145deg,#2c003e 0%, #6a11cb 100%);
        border-radius: 20px;
        padding: 25px;
        color: #ffffff;
        text-align: center;
        box-shadow: 0 12px 40px rgba(0,0,0,0.35);
        animation: popUp 0.4s ease-in-out;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      }
      .gift-modal-content h5 {
        font-size: 28px;
        font-weight: bold;
        color: #ffeb3b;
      }
      .gift-modal-content p {
        font-size: 18px;
        margin: 12px 0;
      }
      .btn-glow {
        background: linear-gradient(90deg, #ff9800, #ff5722);
        border: none;
        color: #fff;
        font-weight: bold;
        font-size: 17px;
        padding: 12px 30px;
        border-radius: 50px;
        transition: all 0.3s ease;
        box-shadow: 0 0 20px rgba(255, 193, 7, 0.7);
      }
      .btn-glow:hover {
        box-shadow: 0 0 30px rgba(255, 193, 7, 1);
        transform: scale(1.07);
      }
      @keyframes popUp {
        from { transform: scale(0.9); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
      }
    </style>";

    $sql = "INSERT INTO message_admin (user_id, title, message_content) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $userId, $title, $message);
    $stmt->execute();
    $stmt->close();
}
?>
