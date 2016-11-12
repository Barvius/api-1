
<?php
   $dbhost="localhost";
   $dbuser="u284139327_bvs";
   $dbpass="OxrcC0wx3Q";
   $dbname="u284139327_mh";
   $pdo = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
   $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   $mysql_results = $pdo->prepare("INSERT INTO `stats` (`date`,`28_0a020b08010d`, `28_000004e4fd40`, `28_0415a30f63ff`, `28_0415a30fdfff`,
      `28_00000448abc3`, `35_0000034fab32`, `35_0000034fab33`, `35_000002793ac7`, `35_000002793ac8`, `81_000000000001`,
      `81_000000000002`) VALUES (:data,:28_0a020b08010d,:28_000004e4fd40,:28_0415a30f63ff,:28_0415a30fdfff,
        :28_00000448abc3,:35_0000034fab32,:35_0000034fab33,:35_000002793ac7,:35_000002793ac8,:81_000000000001,
        :81_000000000002)");
   //$mysql_results->bindParam(':name', 'max_temp');
   $mysql_results->bindParam(':data',  $_GET['data']);
   $mysql_results->bindParam(':28_0a020b08010d',  $_GET['28_0a020b08010d']);
   $mysql_results->bindParam(':28_000004e4fd40',  $_GET['28_000004e4fd40']);
   $mysql_results->bindParam(':28_0415a30f63ff',  $_GET['28_0415a30f63ff']);
   $mysql_results->bindParam(':28_0415a30fdfff',  $_GET['28_0415a30fdfff']);
   $mysql_results->bindParam(':28_00000448abc3',  $_GET['28_00000448abc3']);
   $mysql_results->bindParam(':35_0000034fab32',  $_GET['35_0000034fab32']);
   $mysql_results->bindParam(':35_0000034fab33',  $_GET['35_0000034fab33']);
   $mysql_results->bindParam(':35_000002793ac7',  $_GET['35_000002793ac7']);
   $mysql_results->bindParam(':35_000002793ac8',  $_GET['35_000002793ac8']);
   $mysql_results->bindParam(':81_000000000001',  $_GET['81_000000000001']);
   $mysql_results->bindParam(':81_000000000002',  $_GET['81_000000000002']);
   $mysql_results->execute();
