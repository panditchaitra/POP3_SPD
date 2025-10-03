<?php
session_start();

// MySQL connection
$host = "localhost";
$dbname = "pop3_db";
$user = "root";      // replace with your MySQL username
$pass = "";          // replace with your MySQL password

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle AJAX requests
if(isset($_GET['action'])){
    $action = $_GET['action'];

    if($action=='login'){
        $data = json_decode(file_get_contents('php://input'), true);
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        $stmt = $db->prepare("SELECT id FROM users WHERE username=? AND password=?");
        $stmt->execute([$username,$password]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row){
            $_SESSION['user_id'] = $row['id'];
            echo json_encode(["success"=>true]);
        } else {
            echo json_encode(["success"=>false]);
        }
        exit;
    } 
    elseif($action=='emails'){
        if(!isset($_SESSION['user_id'])) { echo json_encode([]); exit; }
        $user_id = $_SESSION['user_id'];

        $stmt = $db->prepare("SELECT id,sender,subject FROM emails WHERE user_id=?");
        $stmt->execute([$user_id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($rows);
        exit;
    }
    elseif($action=='read_email'){
        if(!isset($_SESSION['user_id'])) { http_response_code(403); echo json_encode(["error"=>"Not logged in"]); exit; }
        $user_id = $_SESSION['user_id'];
        $msg_id = $_GET['id'] ?? 0;

        $stmt = $db->prepare("SELECT sender,subject,body FROM emails WHERE id=? AND user_id=?");
        $stmt->execute([$msg_id,$user_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row){
            // Delete after reading
            $stmt = $db->prepare("DELETE FROM emails WHERE id=? AND user_id=?");
            $stmt->execute([$msg_id,$user_id]);
            echo json_encode($row);
        } else {
            http_response_code(404);
            echo json_encode(["error"=>"Email not found"]);
        }
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>POP3 Web Client</title>
<style>
body { font-family: Arial, sans-serif; background: #f0f0f0; padding: 20px; display:flex; justify-content:center; align-items:center; min-height:100vh; }
#login, #inbox { width: 100%; max-width:450px; background:#fff; padding:30px; border-radius:12px; box-shadow:0 8px 20px rgba(0,0,0,0.15); }
h2 { text-align:center; margin-bottom:20px; }
input[type=text], input[type=password] { width:100%; padding:12px; margin-bottom:15px; border-radius:6px; border:1px solid #ccc; font-size:16px; }
input:focus { border-color:#2575fc; outline:none; }
button { width:100%; padding:12px; border:none; border-radius:6px; background:#2575fc; color:#fff; font-size:16px; cursor:pointer; transition:0.3s; }
button:hover { background:#1a5ed0; }
#login-msg { color:red; text-align:center; margin-top:10px; }
ul { list-style:none; padding:0; max-height:250px; overflow-y:auto; margin-bottom:20px; }
li { padding:12px; border-bottom:1px solid #eee; cursor:pointer; transition:0.2s; }
li:hover { background:#f0f8ff; }
#email-content { background:#f9f9f9; padding:15px; border-radius:6px; white-space:pre-wrap; border:1px solid #eee; }
</style>
</head>
<body>

<div id="login">
<h2>Login</h2>
<input type="text" id="username" placeholder="Username">
<input type="password" id="password" placeholder="Password">
<button onclick="login()">Login</button>
<p id="login-msg"></p>
</div>

<div id="inbox" style="display:none;">
<h2>Inbox</h2>
<ul id="emails"></ul>
<div id="email-content"></div>
</div>

<script>
async function login() {
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;

    const res = await fetch('?action=login',{
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({username,password})
    });
    const data = await res.json();
    if(data.success){
        document.getElementById('login').style.display='none';
        document.getElementById('inbox').style.display='block';
        loadEmails();
    } else {
        document.getElementById('login-msg').innerText="Invalid credentials!";
    }
}

async function loadEmails(){
    const res = await fetch('?action=emails');
    const emails = await res.json();
    const ul = document.getElementById('emails');
    ul.innerHTML='';
    emails.forEach(e=>{
        const li=document.createElement('li');
        li.innerText = e.subject + " (" + e.sender + ")";
        li.onclick = ()=>readEmail(e.id);
        ul.appendChild(li);
    });
}

async function readEmail(id){
    const res = await fetch('?action=read_email&id='+id);
    const data = await res.json();
    document.getElementById('email-content').innerText = "From: "+data.sender+"\nSubject: "+data.subject+"\n\n"+data.body;
}
</script>

</body>
</html>
