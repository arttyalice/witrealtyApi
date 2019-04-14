<?php
$url = 'https://witrealty.co/api/';
// $url = 'http://localhost:5001/www/api/';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\UploadedFileInterface as UploadedFile;

$app->group('/estates', function() {
    //get all estate
    $this->get('', function(Request $req, Response $res) {
        $sql = "SELECT * FROM estate ORDER BY addDate desc";
        $sql_imgs = "SELECT * FROM estate_images WHERE estate_id = ";
        try {
            $db = new db();
            $db = $db->connect();
            $stm = $db->query($sql);
            $estate = $stm->fetchAll(PDO::FETCH_ASSOC);
            $db = null;
            $db = new db();
            $db = $db->connect();
            for($i = 0; $i < count($estate); $i++) {
                $stm2 = $db->query($sql_imgs.'\''.$estate[$i]['estate_id'].'\'');
                $imgs = $stm2->fetchAll(PDO::FETCH_ASSOC);
                array_push($estate[$i], array("imgs"=>$imgs));
            }

            header('Content-type: application/json');
            echo json_encode($estate);
        } catch(PDOException $err) {
            echo '{"error" : {"text": '.$err->getMessage().'}}';
        }
    });
    $this->get('/{id}', function(Request $req, Response $res) {
        $id = strval($req->getAttribute('id'));
        $sql = "SELECT * FROM estate WHERE estate_id = '$id'";
        $sql_imgs = "SELECT * FROM estate_images WHERE estate_id = '$id'";
        try {
            $db = new db();
            $db = $db->connect();
            $stm = $db->query($sql);
            $estate = $stm->fetchAll(PDO::FETCH_ASSOC);
            $db = null;

            $db = new db();
            $db = $db->connect();
            $stm2 = $db->query($sql_imgs);
            $imgs = $stm2->fetchAll(PDO::FETCH_ASSOC);
            array_push($estate[0], array("imgs"=>$imgs));

            header('Content-type: application/json');
            echo json_encode($estate);
        } catch(PDOException $err) {
            echo '{"error" : {"text": '.$err->getMessage().'}}';
        }
    });

    $this->get('/building/suggest', function(Request $req, Response $res) {
        $sql = "SELECT estate_title FROM estate GROUP BY estate_title ORDER BY estate_title asc";
        try {
            $db = new db();
            $db = $db->connect();
            $stm = $db->query($sql);
            $estate = $stm->fetchAll(PDO::FETCH_ASSOC);
            $db = null;

            $data = array();
            for($i = 0; $i < count($estate); $i++) {
                array_push($data, array('title' => $estate[$i]['estate_title']));
            }

            header('Content-type: application/json');
            echo json_encode($data);
        } catch(PDOException $err) {
            echo '{"error" : {"text": '.$err->getMessage().'}}';
        }
    });

    $this->post('/image', function(Request $req, Response $res) {
        $estate_img = $req->getUploadedFiles();
        $uuid = $req->getParam('id');
        $sql_insertImg = "INSERT INTO estate_images (
            estate_id,
            img_base
        ) VALUES (
            '$uuid',
            :img
        )";

        // // // Save image // // //
        // // mkdir by estate_id if not exist // //
        mkdir('./imgs/estate/estate'.$uuid);
        // // // insert new image // // //
        $db = new db();
        $db = $db->connect();
        $stm = $db->prepare($sql_insertImg);
        $fileName = uniqid();
        $data = $estate_img['img'];
        $data->moveTo('./imgs/estate/estate'.$uuid. '/' . $fileName . '.jpg');
        $stm->execute([':img'=>$GLOBALS['url'].'imgs/estate/estate'.$uuid.'/'.$fileName.'.jpg']);
        
        // // // Add WaterMark // // //
        $files = glob('./imgs/estate/estate'.$uuid. '/'.$fileName.'.jpg');
        foreach($files as $file) {
            $img = imagecreatefromstring(file_get_contents($file));
            $stamp = imagecreatefrompng('./src/routes/assets/stamp.png');
        
            $marge_right = 10;
            $marge_bottom = 10;
            $sx = imagesx($stamp);
            $sy = imagesy($stamp);
            
            //Resize Stamp Image
            $width = imagesx($img) * 0.3;
            $height = $width / ($sx / $sy);

            echo 'width: '.$width.', height: '.$height;
            $stamp_Resize = imagecreatetruecolor($width, $height);
            imagealphablending($stamp_Resize, false);
            imagesavealpha($stamp_Resize, true);
            imagecopyresampled($stamp_Resize, $stamp, 0, 0, 0, 0, $width, $height, ImagesX($stamp), ImagesY($stamp));

            imagecopy($img, $stamp_Resize, imagesx($img) - $width - $marge_right, imagesy($img) - $height - $marge_bottom, 0, 0, imagesx($stamp_Resize), imagesy($stamp_Resize));


            imagejpeg($img,$file);
            imagedestroy($img);
            imagedestroy($stamp_Resize);
            imagedestroy($stamp);

            // echo "{'data': 'Success'}";
        }
    });

    //insert new estate
    $this->post('', function(Request $req, Response $res) {
        $estate_id = $req->getParam('id');
        $estate_title = $req->getParam('title');
        $estate_type_id = $req->getParam('type_id');
        $estate_size = $req->getParam('size');
        $estate_bedroom = $req->getParam('bedroom');
        $estate_bathroom = $req->getParam('bathroom');
        $estate_sale_type = $req->getParam('sale_type');
        $estate_price = $req->getParam('price');
        $estate_address = $req->getParam('address');
        $estate_description = $req->getParam('description');
        $estate_create_date = $req->getParam('create_date');
        $estate_furniture = $req->getParam('furniture');
        $estate_price_sqm = $req->getParam('price_sqm');
        $estate_ref_code = $req->getParam('refCode');
        $estate_address_floor = $req->getParam('floorAdd');

        $uuid = uniqid();
        $sql = "INSERT INTO estate
        (
            estate_id,
            create_date,
            furniture,
            price_sqm,
            estate_title,
            estate_type_id,
            estate_size,
            estate_bedroom,
            estate_bathroom,
            estate_sale_type,
            estate_price,
            estate_address,
            estate_description,
            estate_refcode,
            estate_address_floor,
            addDate
        ) VALUES
        (
            '$estate_id',
            '$estate_create_date',
            '$estate_furniture',
            $estate_price_sqm,
            :title,
            :type_id,
            :size,
            :bedroom,
            :bathroom,
            :sale_type,
            :price,
            :address,
            :description,
            :refCode,
            :address_floor,
            NOW()
        )";
        try {
            $db = new db();
            $db = $db->connect();
            $stm = $db->prepare($sql);
            $stm->bindParam(':title', $estate_title);
            $stm->bindParam(':type_id', $estate_type_id);
            $stm->bindParam(':size', $estate_size);
            $stm->bindParam(':bedroom', $estate_bedroom);
            $stm->bindParam(':bathroom', $estate_bathroom);
            $stm->bindParam(':sale_type', $estate_sale_type);
            $stm->bindParam(':price', $estate_price);
            $stm->bindParam(':address', $estate_address);
            $stm->bindParam(':description', $estate_description);
            $stm->bindParam(':refCode', $estate_ref_code);
            $stm->bindParam(':address_floor', $estate_address_floor);
            $stm->execute();

            $db = null;
            echo '{"notice": {"text": "Success"}';
        } catch(PDOException $err) {
            echo '{"error" : {"text": '.$err->getMessage().'}}';
        }
    });
    //Update estate
    $this->post('/update/{id}', function(Request $req, Response $res) {
        $id = strval($req->getAttribute('id'));
        $estate_title = $req->getParam('title');
        $estate_type_id = $req->getParam('type_id');
        $estate_size = $req->getParam('size');
        $estate_bedroom = $req->getParam('bedroom');
        $estate_bathroom = $req->getParam('bathroom');
        $estate_sale_type = $req->getParam('sale_type');
        $estate_price = $req->getParam('price');
        $estate_address = $req->getParam('address');
        $estate_description = $req->getParam('description');
        $estate_create_date = $req->getParam('create_date');
        $estate_furniture = $req->getParam('furniture');
        $estate_price_sqm = $req->getParam('price_sqm');
        $estate_refcode = $req->getParam('refCode');
        $estate_address_floor = $req->getParam('floorAdd');
        $estate_img = json_decode($req->getParam('img'));

        $sql = "UPDATE estate SET
            estate_title = :title,
            estate_type_id = :type_id,
            estate_size = :size,
            estate_bedroom = :bedroom,
            estate_bathroom = :bathroom,
            estate_sale_type = :sale_type,
            estate_price = :price,
            estate_address = :address,
            estate_description = :description,
            price_sqm = $estate_price_sqm,
            furniture = '$estate_furniture',
            create_date = '$estate_create_date',
            estate_refcode = '$estate_refcode',
            estate_address_floor = '$estate_address_floor'
            WHERE estate_id = '$id'";
        try {
            $db = new db();
            $db = $db->connect();
            $stm = $db->prepare($sql);
            $stm->bindParam(':title', $estate_title);
            $stm->bindParam(':type_id', $estate_type_id);
            $stm->bindParam(':size', $estate_size);
            $stm->bindParam(':bedroom', $estate_bedroom);
            $stm->bindParam(':bathroom', $estate_bathroom);
            $stm->bindParam(':sale_type', $estate_sale_type);
            $stm->bindParam(':price', $estate_price);
            $stm->bindParam(':address', $estate_address);
            $stm->bindParam(':description', $estate_description);
            $stm->execute();

            $files = glob('./imgs/estate/estate'.$id.'/*');
            $arr1 = array();
            for ($i = 0; $i < count($estate_img); $i++) {
                if(is_object($estate_img[$i])) {
                    if(is_file(str_replace($GLOBALS['url'], './',$estate_img[$i]->img_base))) {
                        array_push($arr1, str_replace($GLOBALS['url'], './',$estate_img[$i]->img_base));
                    } else {
                        $reqDel = $estate_img[$i]->img_base;
                        $sql_del_img = "DELETE FROM estate_images WHERE img_base = '$reqDel'";
                        $stm = $db->prepare($sql_del_img);
                        $stm->execute();
                    }
                }
            }
            $deleteFile = array_diff($files, $arr1);

            foreach($deleteFile as $file) {
                if(is_file($file)) {
                    unlink($file);
                    $reqDel = str_replace('./', $GLOBALS['url'], $file);
                    $sql_del_img = "DELETE FROM estate_images WHERE img_base = '$reqDel'";
                    $stm = $db->prepare($sql_del_img);
                    $stm->execute();
                }
            }

            $db = null;
            header('Content-type: application/json');
            echo '{"notice": {"text": "Success"}';
        } catch(PDOException $err) {
            echo '{"error" : {"text": '.$err->getMessage().'}}';
        }
    });
    //Delete estate
    $this->post('/delete', function(Request $req, Response $res){
        $id = json_decode(strval($req->getParam('id')));
        try{
            $db = new db();
            $db = $db->connect();
            for($i = 0; $i < count($id); $i++) {
                $sql = "DELETE FROM estate WHERE estate_id = '$id[$i]'";
                $sql2 = "DELETE FROM estate_images WHERE estate_id = '$id[$i]'";
                $stmt = $db->prepare($sql);
                $stmt->execute();
                $stmt = $db->prepare($sql2);
                $stmt->execute();
                $files = glob('./imgs/estate/estate'.$id[$i]. '/*');
                foreach ($files as $file) {
                    if (is_dir($file)) {
                        self::deleteDir($file);
                    } else {
                        unlink($file);
                    }
                }
                rmdir('./imgs/estate/estate'.$id[$i]);
            }
            $db = null;
            header('Content-type: application/json');
            echo '{"notice": {"text": "Success"}';
        } catch(PDOException $e){
            echo '{"error": {"text": '.$e->getMessage().'}';
        }
    });
});
