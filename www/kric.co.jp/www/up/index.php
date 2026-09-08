<html>  
  <head>  
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />  
    <meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=2.0,user-scalable=1" />  
    <title>ファイルアップロードテスト</title>  
  </head>  
<body>  
  <center>  
    <div style='margin: 100 auto;';>  
<?php  
// ★条件：$_FILESがある場合  
if($_FILES){  
  if (is_uploaded_file($_FILES["upfile"]["tmp_name"])) {  
    // アップロードされたファイルの削除  
    if (unlink($_FILES["upfile"]["tmp_name"])) {  
  
      // アップロード完了時の時刻取得  
      $upload_time = microtime(TRUE) - $_SERVER['REQUEST_TIME_FLOAT'];  
  
      // アップロードされたファイルのサイズ取得  
      $MB = round($_FILES['upfile']['size'] / $upload_time / 1024 / 1024, 2);  
      $mbps = round($MB * 8,2);  
  
      echo '<p>' . $_FILES['upfile']['name'] . 'をアップロードしました。</p>';  
      print 'アップロードにかかった時間：<br><h2>' . round($upload_time,2) . ' 秒</h2></p>';  
      print 'アップロードしたサイズ：<br><h2>' . number_format($_FILES['upfile']['size']) . ' Bytes</h2></p>';  
      print '速度(Mbps)：<br><h2>' . $mbps . ' Mbps</h2></p>';  
      print '速度(MB/s)：<br><h2>' . $MB . ' MB/s</h2></p>';  
    } else {  
      echo '<p>ファイルをアップロードできません。</p>';  
    }  
  } else {  
    echo '<p>ファイルが選択されていません。</p>';  
  }  
  echo "<a href='./'>戻る</a>";  
// ★条件：$_FILESがない場合  
}else{  
?>  
    <form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post" enctype="multipart/form-data">  
      ファイル：<br />  
      <input type="file" name="upfile" size="30" /><br />  
      <br />  
      <input type="submit" value="アップロード" />  
    </form>  
<?php  
}  
?>  
    </div>  
  </center>  
</body>  
</html>