<?php
    $kind = array();
    $kind[1] = '質問';
    $kind[2] = 'ご意見';
    $kind[3] = '資料請求';

    session_start();
    $mode = 'input';
    $errmessage = array();
    if (isset($_POST['back']) && $_POST['back']) {
        // なにもしない
    } elseif (isset($_POST['confirm']) && $_POST['confirm']) {
        // 確認画面
        if ( !$_POST['fullname'] ) {
            $errmessage[] = "名前を入力してください";
        } elseif (mb_strlen($_POST['fullname']) > 100) {
            $errmessage[] = "名前は100文字以内にしてください";
        }
        $_SESSION['fullname'] = htmlspecialchars($_POST['fullname'], ENT_QUOTES);

        if ( !$_POST['email'] ) {
            $errmessage[] = "Eメールを入力してください";
        } elseif (mb_strlen($_POST['email']) > 200) {
            $errmessage[] = "Eメールは200文字以内にしてください";
        } elseif ( !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) ) {
            $errmessage[] = "Eメールアドレスが不正です";
        }
        $_SESSION['email'] = htmlspecialchars($_POST['email'], ENT_QUOTES);

        if ( !$_POST['mkind'] ) {
            $errmessage[] = "種別を入力してください";
        } elseif ($_POST['mkind'] <= 0 || $_POST['mkind'] >= 4 ) {
            $errmessage[] = "種別が不正です";
        }
        $_SESSION['mkind'] = htmlspecialchars($_POST['mkind'], ENT_QUOTES);

        if ( !$_POST['message'] ) {
            $errmessage[] = "お問い合わせ内容を入力してください";
        } elseif (mb_strlen($_POST['message']) > 500) {
            $errmessage[] = "お問い合わせ内容は500文字以内にしてください";
        } 
        $_SESSION['message'] = htmlspecialchars($_POST['message'], ENT_QUOTES);
        
        if ($errmessage) {
            $mode = 'input';
        } else {
            $token = bin2hex(random_bytes(32));
            $_SESSION['token'] = $token;
            $mode = 'confirm';
        }

    } elseif (isset($_POST['send']) && $_POST['send']) {
        // 送信ボタンを押したとき
        if ( !$_POST['token'] || !$_SESSION['token'] || !$_SESSION['email']) {
            $errmessage[] = "不正な処理が行われました";
            $_SESSION = array();
            $mode     = 'input';
        } elseif ($_POST['token'] != $_SESSION['token']) {
            $errmessage[] = "不正な処理が行われました";
            $_SESSION = array();
            $mode     = 'input';
        } else {
            $message = "お問い合わせを受け付けました \r\n"
                        . "名前:" . $_SESSION['fullname'] . "\r\n"
                        . "email:" . $_SESSION['email'] . "\r\n"
                        . "種別:" . $kind[$_SESSION['mkind']] . "\r\n"
                        . "お問い合わせ内容: \r\n"
                        .preg_replace("/\r\n|\r|\n/", "\r\n", $_SESSION['message']);
            mail($_SESSION['email'], 'お問い合わせありがとうございました。', $message);
            mail('ogikazu2006@yahoo.co.jp', 'お問い合わせありがとうございました。', $message);
            $_SESSION = array();
            $mode = 'send';
        }
    } else {
        $_SESSION = array();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>お問い合わせフォーム</title>
</head>
<body>
    <?php if ($mode == 'input'): ?>
        <!-- 入力画面 -->
        <?php
        if ($errmessage) {
            echo '<div style="color:red;">';
            echo implode('<br>', $errmessage);
            echo '</div>';
        }
        ?>
        <form action="./index.php" method="post">
            名前<input type="text" name="fullname" value="<?php echo $_SESSION['fullname'] ?>"><br>
            Eメール<input type="email" name="email" value="<?php echo $_SESSION['email'] ?>"> <br>
            種別
            <select name="mkind">
                <?php foreach ($kind as $i => $v): ?>
                    <?php if ($_SESSION['mkind'] == $i): ?>
                        <option value="<?php echo $i ?>" selected><?php echo $v ?></option>                    
                    <?php else: ?>
                        <option value="<?php echo $i ?>"><?php echo $v ?></option>                    
                    <?php endif ?>                    
                <?php endforeach ?>
            </select><br>
            お問い合わせ内容<br>
            <textarea name="message" cols="40" rows="8"><?php echo $_SESSION['message'] ?></textarea><br>
            <input type="submit" name="confirm" value="確認">
        </form>

    <?php elseif ($mode == 'confirm'): ?>
        <!-- 確認画面 -->
        <form action="./index.php" method="post">
            <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?>">
            名前    <?php echo $_SESSION['fullname'] ?><br>
            Eメール <?php echo $_SESSION['email'] ?><br>
            種別    <?php echo $kind[$_SESSION['mkind']] ?><br>
            お問い合わせ内容<br>
            <?php echo nl2br($_SESSION['message']) ?><br>
            <input type="submit" name="back" value="戻る">
            <input type="submit" name="send" value="送信">
        </form>
    <?php else: ?>
        <!-- 完了画面 -->
        送信しました。お問い合わせありがとうございました。
    <?php endif ?>
</body>
</html>