<?php

$dataFile = 'bbs.dat';

//CSRF対策
session_start();

function setToken(){
  $token = sha1(uniqid(mt_rand(), true));
  $_SESSION['token'] = $token;
}

function checkToken(){
  if(empty($_SESSION['token']) || ($_SESSION['token'] != $_POST['token'])){
    echo "不正なPOSTが行われました";
    exit;
  }
}

function h($s){
  return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

//messageやuserがセットされているかどうか確認
if($_SERVER['REQUEST_METHOD'] == 'POST' &&
    isset($_POST['message']) &&
    isset($_POST['user'])){//決まり文句

  checkToken();

  $message = trim($_POST['message']);
  $user = trim($_POST['user']);

  //データを書き込むのはmessageが入ってるときだけにする

  if($message !== ''){

      //userが空だった場合適当な名前を作る
      $user = ($user === '')?'ななしさん':$user;

      //messageとuserに\tがきたときに別の処理におきかえる
      $message = str_replace("\t", ' ', $message);
      $user = str_replace("\t", ' ', $user);

      //投稿した日付と時間
      $postedAt = date('Y-m-d H:i:s');

      $newData = $message."\t".$user."\t".$postedAt."\n";

      $fp = fopen($dataFile, 'a');
      fwrite($fp, $newData);
      fclose($fp);
  }
} else {
  setToken();
}
//ファイル全体を読み込んでFILE_IGNORE_NEW_LINESで配列の各要素の最後に改行文字を追加しません。
$posts = file($dataFile, FILE_IGNORE_NEW_LINES);
//投稿を逆順にする
$posts = array_reverse($posts);

?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>おれんじ掲示板</title>
</head>
<body>
  <h1>おれんじ掲示板</h1>
  <form action="" method="post">
    message: <input type="text" name="message">
    user: <input type="text" name="user">
    <input type="submit" value="投稿">
    <input type="hidden" name="token" value="<?php echo h($_SESSION['token']); ?>">
  </form>
  <h2>投稿一覧（<?php echo count($posts); ?>件）</h2>
  <ul>
    <?php if(count($posts)) : ?>
        <?php foreach($posts as $post) : ?>
          <?php list($message, $user, $postedAt) = explode("\t", $post); ?>
          <li><?php echo h($message); ?> (<?php echo h($user); ?>) - <?php echo h($post); ?></li>
        <?php endforeach; ?>
    <?php else : ?>
    <li>まだ投稿はありません。</li>
  <?php endif; ?>
  </ul>
</body>
</html>
