<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>mission.5-2</title>
</head>
<body>
    <strong>掲示板</strong><br>
    &emsp;&emsp;&emsp;&emsp;&emsp;編集や削除をする場合は、投稿番号と書き込み時に指定したパスワードを入力してね<br>
    <form action="" method="post" id="form1">
    </form>
    <form action="" method="post" id="form2">
    </form>
    <form action="" method="post" id="form3">
    </form>
    <label for="name">名前</label>&emsp;&emsp;&emsp;&emsp;
    <input type="text" id="name" name="name" placeholder="名前を入力" form="form1">
    &emsp;&emsp;&emsp;
    <?php
        if(empty($_POST["editnumber"])){
            if(!empty($_POST["password"])){
                if(strlen($_POST["password"]) !== 4){
                    echo '<font color="red">パスワードは数字4桁で入力してください</font>';
                }
            }else{
                if(!empty($_POST["name"]) && !empty($_POST["comment"])){
                    echo '<font color="red">数字4桁のパスワードを入力してください</font>';
                }
            }
        }
        ?>
    <br>
    <label for="comment">コメント</label>&emsp;&emsp;
    <input type="text" id="comment" name="comment" placeholder="コメントを入力" form="form1">
    <br>
    <label for="password">パスワード</label>&emsp;
    <input type="tel" id="password" name="password" style="width:35px;" maxlength="4" placeholder="数字" form="form1">
    <input type="submit" value="送信" name="submit" form="form1">
    <br>
    <br>
    <label for="deletenum">削除フォーム</label>
    <input type="tel" id="delete" name="delete" size="4" placeholder="投稿番号" form="form2">
    <br>
    &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;
    <input type="tel" id="password" name="password" style="width:35px;" maxlength="4" placeholder="パス" form="form2">
    <input type="submit" value="削除" name="submit2" form="form2">
    <br>
    <label for="edit">編集フォーム</label>
    <input type="tel" id="edit" name="edit" size="4" placeholder="投稿番号" form="form3">
    <br>
    &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;
    <input type="tel" id="password" name="password" style="width:35px;" maxlength="4" placeholder="パス" form="form3">
    <input type="submit" value="編集" name="submit3" form="form3">
    <br>
    <br>
    <strong>行ってみたい日本の場所</strong>
    <br>

<?php
	//データベース接続
$dsn = 'データベース名';
	$user = 'ユーザー名';
	$password = 'パスワード';
	$pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
	//テーブル作成
	$sql="CREATE TABLE IF NOT EXISTS table51"
	."("
	."id INT AUTO_INCREMENT PRIMARY KEY,"
	."name char(30) NOT NULL,"
	."comment TEXT NOT NULL,"
	."datetime DATETIME(3) NOT NULL,"
	."password INT(4) UNSIGNED NOT NULL"
	.");";
	$stmt = $pdo->query($sql);

	//idは自動入力でデータのキー
	//名前は文字列30桁以内、パスワードは4桁以内で負の数は無し

	/* テーブル構成確認
	$sql ='SHOW CREATE TABLE table51';
	$result = $pdo -> query($sql);
	foreach ($result as $row){
		echo $row[1];
	}
	echo "<hr>";
	*/

	//データベース内容表示を関数として定義
    function display($pdo){
        $sql='SELECT id,name,comment,date_format(datetime, "%Y/%m/%d %H:%i:%s.%f") as datetime2, password FROM table51';
	    $stmt=$pdo->query($sql);
	    $results=$stmt->fetchall(PDO::FETCH_ASSOC);
	    foreach($results as $content){
            $ex=explode(".",$content["datetime2"]);
            $milli=substr($ex[1],0,2);
            $finaldate=$ex[0].".".$milli;
		    echo $content["id"]." ".$content["name"]." ".$content["comment"]." ".$finaldate."<br>";
	    }
    }
    
    //既存のデータベース内容表示
    if(empty($_POST["delete"]) && empty($_POST["edit"]) && empty($_POST["editnumber"])){
        display($pdo);
    }

	//新規投稿処理
	if(!empty($_POST["name"]) && !empty($_POST["comment"]) && strlen($_POST["password"])==4 && empty($_POST["editnumber"])){
	    //データベース書き込み
        $dt=new datetime();
        $datetime=$dt->format("Y/m/d H:i:s.v");
	    $name=$_POST["name"];
	    $comment=$_POST["comment"];
	    $password=$_POST["password"];
	    $sql=$pdo->prepare("INSERT INTO table51 (name,comment,datetime,password) VALUES (:name,:comment,:datetime,:password)");
	    $sql->bindParam(':name',$name,PDO::PARAM_STR);
        $sql->bindParam(':comment',$comment,PDO::PARAM_STR);
        $sql->bindParam(':datetime',$datetime,PDO::PARAM_STR);
        $sql->bindParam(':password',$password,PDO::PARAM_INT);
        $sql->execute();
        //データベースから最新の投稿を表示
        $sql='SELECT id,name,comment,date_format(datetime, "%Y/%m/%d %H:%i:%s.%f") as datetime2, password FROM table51 WHERE id=(SELECT MAX(id) FROM table51)'; //idが最大のカラムから数値を取得し、その数値とidが一致する行(idが最大の行)の内容を抽出
        $stmt=$pdo->query($sql);
        $results=$stmt->fetch(PDO::FETCH_ASSOC);
        $ex=explode(".",$results["datetime2"]);
        $milli=substr($ex[1],0,2);
        $finaldate=$ex[0].".".$milli;
        echo $results["id"]." ".$results["name"]." ".$results["comment"]." ".$finaldate."<br>";
    }

    //投稿削除処理
    if(!empty($_POST["delete"]) && !empty($_POST["password"])){
        $deletenumber=$_POST["delete"];
        $sql='SELECT * FROM table51 WHERE id=:deletenumber';
        $stmt=$pdo->prepare($sql);
        $stmt->bindParam(':deletenumber',$deletenumber,PDO::PARAM_INT);
        $stmt->execute();
        $results=$stmt->fetch(PDO::FETCH_ASSOC); //削除対象の投稿を取得
        if($_POST["password"]==$results["password"]){ //そもそも投稿がなく、$results["password"]がない時はelseにいく
            $sql='DELETE from table51 WHERE id=:deletenumber';
            $stmt=$pdo->prepare($sql);
            $stmt->bindParam(':deletenumber',$deletenumber,PDO::PARAM_INT);
            $stmt->execute(); //投稿を削除
            display($pdo); //データベース内容表示
        }else{
            echo '<font color="red">パスワードが違う、あるいは投稿が存在しません。<br></font>';
            display($pdo);
        }
    }else{ //この処理により、パスワードが空の状態で削除番号を送信したときにも表示が消えない
        if(!empty($_POST["delete"]) && empty($_POST["password"])){
            echo '<font color="red">パスワードを入力してください。<br></font>';
            display($pdo);
        }
    }

    //編集指示処理
    if(!empty($_POST["edit"]) && !empty($_POST["password"])){
        $edit=$_POST["edit"];
        $sql='SELECT * FROM table51 WHERE id=:id';
        $stmt=$pdo->prepare($sql);
        $stmt->bindParam(':id',$edit,PDO::PARAM_INT);
        $stmt->execute();
        $results=$stmt->fetch(PDO::FETCH_ASSOC); //編集投稿を取得
        if($results["password"]==$_POST["password"]){ //パスワード一致
            echo "<strong><br>編集状態です。<br></strong>";
            echo "名前とコメント欄に編集内容を入力して送信してください。投稿番号は変更されません。再度のパスワード入力は不要です。<br><br>";
            echo "<strong>編集対象投稿</strong><br>";
            echo "投稿番号： ".$results["id"]."<br>";
            echo "名前： ".$results["name"]."<br>";
            echo "コメント： ".$results["comment"]."<br>";
        }else{ //パスワード不一致
            echo  '<font color="red">パスワードが違う、あるいは投稿が存在しません。<br></font>';
            display($pdo);
        }
    }else{  //パスワード未入力
        if(!empty($_POST["edit"]) && empty($_POST["password"])){
            echo '<font color="red">パスワードを入力してください。<br></font>';
            display($pdo);
        }
    }

    //編集処理
    if(!empty($_POST["name"]) && !empty($_POST["comment"]) && !empty($_POST["editnumber"])){
        $id=$_POST["editnumber"];
        $name=$_POST["name"];
        $comment=$_POST["comment"];
        $sql='UPDATE table51 SET name=:name, comment=:comment WHERE id=:id';
        $stmt=$pdo->prepare($sql);
        $stmt->bindParam(':id',$id,PDO::PARAM_INT);
        $stmt->bindParam(':name',$name,PDO::PARAM_STR);
        $stmt->bindParam(':comment',$comment,PDO::PARAM_STR);
        $stmt->execute();
        display($pdo);
    }else{ //名前やコメントが未入力で送信された際、投稿が消えない為
        if(!empty($_POST["editnumber"])){
            if(empty($_POST["name"]) || empty($_POST["comment"])){
                echo '<font color="red">名前とコメントが未入力です。もう一度編集番号とパスワードを入力してください。<br></font>';
                display($pdo);
            }
        }
    }

?>

<input type="hidden" name="editnumber" 
value="<?php 
        if(!empty($_POST["edit"]) && !empty($_POST["password"])){ //編集フォームとパスワードが入力されているとき
            $edit=$_POST["edit"];
			$sql='SELECT * FROM table51 WHERE id=:edit';
			$stmt=$pdo->prepare($sql);
			$stmt->bindParam(':edit',$edit,PDO::PARAM_INT);
			$stmt->execute();
			$results = $stmt->fetch(PDO::FETCH_ASSOC);
			if($_POST["password"]==$results["password"]){
				echo $edit;
			}
		}
        ?>" 
form="form1"> 
    <!- 最終的にform1の送信と共に送信する ->

</body>
</html>