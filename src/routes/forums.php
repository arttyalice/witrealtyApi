<?php
$url = 'https://witrealty.co/api/';
// $url = 'http://localhost:5001/www/api/';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\UploadedFileInterface as UploadedFile;

$app->add(function ($req, $res, $next) {
    $response = $next($req, $res);
    return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
})

//Forums Article News
$app->group('/forums', function() {
    //get all forums
    $this->get('', function(Request $req, Response $res) {
        $sql = "SELECT * FROM forums";

        try {
            $db = new db();
            $db = $db->connect();
            $stm = $db->query($sql);
            $forums = $stm->fetchAll(PDO::FETCH_ASSOC);
            $db = null;
            $db = new db();
            $db = new db();
            $db = $db->connect();
            for($i = 0; $i < count($forums); $i++) {
                $id = $forums[$i]['uniq_id'];
                $sql_imgs = "SELECT * FROM forum_images WHERE uniq_id = '$id'";
                $stm2 = $db->query($sql_imgs);
                $imgs = $stm2->fetchAll(PDO::FETCH_ASSOC);
                array_push($forums[$i], array("imgs"=>$imgs));
            }
            header('Content-type: application/json');
            echo json_encode($forums);
        } catch(PDOException $err) {
            echo '{"error" : {"text": '.$err->getMessage().'}}';
        }
    });
    //get forum by ID
    $this->get('/{id}', function(Request $req, Response $res) {
        $id = $req->getAttribute('id');
        $sql = "SELECT * FROM forums WHERE uniq_id = '$id'";
        $sql_imgs = "SELECT * FROM forum_images WHERE uniq_id = '$id'";
        try {
            $db = new db();
            $db = $db->connect();
            $stm = $db->query($sql);
            $forums = $stm->fetchAll(PDO::FETCH_ASSOC);
            $db = null;
            $db = new db();
            $db = new db();
            $db = $db->connect();
            for($i = 0; $i < count($forums); $i++) {
                $stm2 = $db->query($sql_imgs);
                $imgs = $stm2->fetchAll(PDO::FETCH_ASSOC);
                array_push($forums[$i], array("imgs"=>$imgs));
            }
            header('Content-type: application/json');
            echo json_encode($forums);
        } catch(PDOException $err) {
            echo '{"error" : {"text": '.$err->getMessage().'}}';
        }
    })
    //Insert forums images
    $this->post('/content/image', function(Request $req, Response $res) {
        $estate_img = $req->getUploadedFiles();
        $uuid = $req->getParam('id');

        // // // Save image // // //
        // // mkdir by estate_id if not exist // //
        mkdir('./imgs/forums/forum'.$uuid);
        // // // insert new image // // //
        $db = new db();
        $db = $db->connect();
        $fileName = uniqid();
        $data = $estate_img['img'];
        $data->moveTo('./imgs/forums/forum'.$uuid. '/' . $fileName . '.jpg');

        // // // Add WaterMark // // //
        $files = glob('./imgs/forums/forum'.$uuid. '/'.$fileName.'.jpg');
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

            $stamp_Resize = imagecreatetruecolor($width, $height);
            imagealphablending($stamp_Resize, false);
            imagesavealpha($stamp_Resize, true);
            imagecopyresampled($stamp_Resize, $stamp, 0, 0, 0, 0, $width, $height, ImagesX($stamp), ImagesY($stamp));

            imagecopy($img, $stamp_Resize, imagesx($img) - $width - $marge_right, imagesy($img) - $height - $marge_bottom, 0, 0, imagesx($stamp_Resize), imagesy($stamp_Resize));


            imagejpeg($img,$file);
            imagedestroy($img);
            imagedestroy($stamp_Resize);
            imagedestroy($stamp);

            echo json_encode($GLOBALS['url'].'imgs/forums/forum'.$uuid.'/'.$fileName.'.jpg');
        }
    });
    $this->post('/header/image', function(Request $req, Response $res) {
        $forum_img = $req->getUploadedFiles();
        $uuid = $req->getParam('id');
        // // // Save image // // //
        // // mkdir by estate_id if not exist // //
        mkdir('./imgs/forums/forum'.$uuid);
        // // // insert new image // // //
        $fileName = uniqid();
        $data = $forum_img['img'];
        $data->moveTo('./imgs/forums/forum'.$uuid. '/' . $fileName . '.jpg');

        $path = $GLOBALS['url'].'imgs/forums/forum'.$uuid.'/'.$fileName.'.jpg';
        $sql = "INSERT INTO forum_images (
            uniq_id,
            img_base
        ) VALUES (
            '$uuid',
            '$path'
        )";
        $db = new db();
        $db = $db->connect();
        $stm = $db->prepare($sql);
        $stm->execute();

        // // // Add WaterMark // // //
        $files = glob('./imgs/forums/forum'.$uuid. '/'.$fileName.'.jpg');
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

            $stamp_Resize = imagecreatetruecolor($width, $height);
            imagealphablending($stamp_Resize, false);
            imagesavealpha($stamp_Resize, true);
            imagecopyresampled($stamp_Resize, $stamp, 0, 0, 0, 0, $width, $height, ImagesX($stamp), ImagesY($stamp));

            imagecopy($img, $stamp_Resize, imagesx($img) - $width - $marge_right, imagesy($img) - $height - $marge_bottom, 0, 0, imagesx($stamp_Resize), imagesy($stamp_Resize));


            imagejpeg($img,$file);
            imagedestroy($img);
            imagedestroy($stamp_Resize);
            imagedestroy($stamp);

        }
        echo '{"notice": {"text": "Header Image Success"}';
    });
    //insert new Forums
    $this->post('', function(Request $req, Response $res) {
        $forum_title = $req->getParam('title');
        $forum_content = $req->getParam('content');
        $uuid = $req->getParam('id');

        $sql = "INSERT INTO forums
        (
            forum_title,
            forum_content,
            uniq_id,
            create_date
        ) VALUES
        (
            '$forum_title',
            '$forum_content',
            '$uuid',
            NOW()
        )";
        try {
            $db = new db();
            $db = $db->connect();
            $stm = $db->prepare($sql);
            $stm->execute();

            echo '{"notice": {"text": "Success"}';
        } catch(PDOException $err) {
            echo '{"error" : {"text": '.$err->getMessage().'}}';
        }
    });
    //Update Forums
    $this->post('/update/{id}', function(Request $req, Response $res) {
        $id = $req->getAttribute('id');
        $forum_title = $req->getParam('title');
        $forum_content = $req->getParam('content');
        $forum_img = json_decode($req->getParam('img'));
        $sql = "UPDATE forums SET
            forum_title = :title,
            forum_content = :content
            WHERE id = $id";
        $sql_deleteImg = "DELETE FROM forum_images WHERE forum_id = $id";
        $sql_insertImg = "INSERT INTO forum_images (
                forum_id,
                img_base
            ) VALUES (
                :forum_id,
                :img
            )";
        try {
            $db = new db();
            $db = $db->connect();
            $stm = $db->prepare($sql);
            $stm->bindParam(':title', $forum_title);
            $stm->bindParam(':content', $forum_content);
            $stm->execute();
            // delete images that bind to forum_id
            $stm = $db->prepare($sql_deleteImg);
            $stm->execute();
            //insert image

            $files = glob('./imgs/forums/forum'.$id. '/*');
            $arr1 = array();
            $arr2 = array();
            for ($i = 0; $i < count($estate_img); $i++) {
                if(is_object($estate_img[$i])) {
                    array_push($arr1, file_get_contents(str_replace($GLOBALS['url'], './',$estate_img[$i]->img_base)));
                } else {
                    array_push($arr1, $estate_img[$i]);
                }
                for ($j = 0; $j < count($files); $j++) {
                    array_push($arr2 , file_get_contents($files[$j]));
                }
            }
            $deleteFiles = array_diff($arr2, $arr1);
            $mergeImgArr = array_merge($arr1, $arr2);
            $mergeImgArr = array_unique($mergeImgArr);
            $imagesArr = array_diff($mergeImgArr, $deleteFiles);
            $estate_img = $imagesArr;
            $estate_img = array_unique($estate_img);

            $files = glob('./imgs/forums/forum'.$id. '/*');
                foreach ($files as $file) {
                    if (is_dir($file)) {
                        self::deleteDir($file);
                    } else {
                        unlink($file);
                    }
                }
            $stm = $db->prepare($sql_insertImg);
            for ($i = 0; $i < count($forum_img); $i++) {
                $stm->execute([':forum_id'=>$id, ':img'=>$forum_img[$i]]);
            }
            $db = null;
            header('Content-type: application/json');
            echo '{"notice": {"text": "Success"}';
        } catch(PDOException $err) {
            echo '{"error" : {"text": '.$err->getMessage().'}}';
        }
    });
    //Delete Forums
    $this->post('/delete', function(Request $req, Response $res){
        $id = json_decode($req->getParam('id'));
        try {
            $db = new db();
            $db = $db->connect();
            for($i = 0; $i < count($id); $i++) {
                $sql = "DELETE FROM forums WHERE uniq_id = '$id[$i]'";
                $sql2 = "DELETE FROM forum_images WHERE uniq_id = '$id[$i]'";
                $stmt = $db->prepare($sql);
                $stmt->execute();
                $stmt = $db->prepare($sql2);
                $stmt->execute();

                $files = glob('./imgs/forums/forum'.$id[$i]. '/*');
                foreach ($files as $file) {
                    if (is_dir($file)) {
                        self::deleteDir($file);
                    } else {
                        unlink($file);
                    }
                }
                rmdir('./imgs/forums/forum'.$id[$i]);
            }
            $db = null;
            header('Content-type: application/json');
            echo '{"notice": {"text": "Success"}';
        } catch(PDOException $e){
            echo '{"error": {"text": '.$e->getMessage().'}';
        }
    });
});
