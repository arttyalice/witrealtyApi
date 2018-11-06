<?php
$url = 'http://witrealty.co/api/';
// $url = 'http://localhost:5001/www/api/';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\UploadedFileInterface as UploadedFile;

// use Slim\Http\Request;
// use Slim\Http\Response;
// use Slim\Http\UploadedFile;

$app = new \Slim\App;

$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

$app->add(function ($req, $res, $next) {
    $response = $next($req, $res);
    return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});


$app->group('/estates', function() {
    //get all estate
    $this->get('', function(Request $req, Response $res) {
        $sql = "SELECT * FROM estate";
        $sql_imgs = "SELECT * FROM estate_images WHERE estate_id = ";
        try {
            $db = new db();
            $db = $db->connect();
            $stm = $db->query($sql);
            $estate = $stm->fetchAll(PDO::FETCH_ASSOC);
            $db = null;
            $db = new db();
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

    $this->post('/image', function(Request $req, Response $res) {
        $uploadedFiles = $req->getUploadedFiles();

        $uploadedFile = $uploadedFiles['img'];
        
        $uploadedFile->moveTo('./imgs/123.jpg');
    });

    function moveUploadedFile($directory, UploadedFile $uploadedFile) {
        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        $basename = random_bytes(8);
        $filename = sprintf('%s.%0.8s', $basename, $extension);

        $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

        return $filename;
    }

    //insert new estate
    $this->post('', function(Request $req, Response $res) {
        $estate_title = $req->getParam('title');
        $estate_type_id = $req->getParam('type_id');
        $estate_size = $req->getParam('size');
        $estate_bedroom = $req->getParam('bedroom');
        $estate_bathroom = $req->getParam('bathroom');
        $estate_sale_type = $req->getParam('sale_type');
        $estate_price = $req->getParam('price');
        $estate_address = $req->getParam('address');
        $estate_description = $req->getParam('description');
        // $estate_img = $req->getUploadedFiles();
        
        $uuid = uniqid();
        $sql = "INSERT INTO estate
        (
            estate_id,
            estate_title,
            estate_type_id,
            estate_size,
            estate_bedroom,
            estate_bathroom,
            estate_sale_type,
            estate_price,
            estate_address,
            estate_description
        ) VALUES
        (
            :e_id,
            :title,
            :type_id,
            :size,
            :bedroom,
            :bathroom,
            :sale_type,
            :price,
            :address,
            :description
        )";
        $sql3 = "INSERT INTO estate_images (
                estate_id,
                img_base
            ) VALUES (
                :estate_id,
                :img
            )";
        try {
            $db = new db();
            $db = $db->connect();
            $stm = $db->prepare($sql);
            $stm->bindParam(':e_id', $uuid);
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

            //Save image
            //mkdir by estate_id if not exist
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
            estate_description = :description
            WHERE estate_id = '$id'";
        $sql_insertImg = "INSERT INTO estate_images (
                estate_id,
                img_base
            ) VALUES (
                '$id',
                :img
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
            $stm->execute();
            // insert image
            //delete old image
            $stm = $db->prepare("DELETE FROM estate_images WHERE estate_id = '$id'");
            $stm->execute();

            $files = glob('./imgs/estate/estate'.$id. '/*');
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

            foreach($files as $file) {
                if(is_file($file)) {
                    unlink($file);
                }
            }
            //insert new image
            $stm = $db->prepare($sql_insertImg);
            for ($i = 0; $i < count($estate_img); $i++) {
                $fileName = uniqid();
                $data = $estate_img[$i];
                $file = './imgs/estate/estate'.$id. '/' . $fileName . '.jpg';
                $success = file_put_contents($file, $data);

                $stm->execute([':img'=>$GLOBALS['url'].'imgs/estate/estate'.$id.'/'.$fileName.'.jpg']);
            }
            $files = glob('./imgs/estate/estate'.$id. '/*');
            foreach($files as $file) {
                $img = imagecreatefromstring(file_get_contents($file));
                $stamp = imagecreatefrompng('./src/routes/assets/stamp.png');
            
                $marge_right = 10;
                $marge_bottom = 10;
                $sx = imagesx($stamp);
                $sy = imagesy($stamp);
            
                imagecopy($img, $stamp, imagesx($img) - $sx - $marge_right, imagesy($img) - $sy - $marge_bottom, 0, 0, imagesx($stamp), imagesy($stamp));
            
                imagejpeg($img,$file);
                imagedestroy($img);
                imagedestroy($stamp);
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
                $id = $forums[$i]['id'];
                $sql_imgs = "SELECT * FROM forum_images WHERE forum_id = '$id'";
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
        $sql = "SELECT * FROM forums WHERE id = $id";
        $sql_imgs = "SELECT * FROM forum_images WHERE forum_id = ";
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
                $stm2 = $db->query($sql_imgs.$forums[$i]['id']);
                $imgs = $stm2->fetchAll(PDO::FETCH_ASSOC);
                array_push($forums[$i], array("imgs"=>$imgs));
            }
            header('Content-type: application/json');
            echo json_encode($forums);
        } catch(PDOException $err) {
            echo '{"error" : {"text": '.$err->getMessage().'}}';
        }
    });
    //insert new Forums
    $this->post('', function(Request $req, Response $res) {
        $uuid = $req->getParam('id');
        $forum_title = $req->getParam('title');
        $forum_content = $req->getParam('content');
        $forum_imgs = json_decode($req->getParam('img'));
        $content_imgs = json_decode($req->getParam('contentImgs'));

        print_r($content_imgs[0]->srcPath);
        
        $sql = "INSERT INTO forums
        (  
            id,
            forum_title,
            forum_content
        ) VALUES
        (
            '$uuid',
            :title,
            :content
        )";
        $sql2 = "INSERT INTO forum_images
        (
            img_base,
            forum_id
        ) VALUES
        (
            :img,
            '$uuid'
        )";
        try {
            $db = new db();
            $db = $db->connect();
            $stm = $db->prepare($sql);
            $stm->bindParam(':title', $forum_title);
            $stm->bindParam(':content', $forum_content);
            $stm->execute();

            //Save image
            //mkdir by forum_id if not exist
            if (is_dir("./imgs/forums/forum".$uuid)) {
                // mkdir("./imgs/forums/forum".$uuid, 0777, true);
            } else {
                mkdir("./imgs/forums/forum".$uuid, 0777, true);
            }
            for ($i = 0; $i < count($forum_imgs); $i++) {
                $stm = $db->prepare($sql2);
                $fileName = uniqid();
                $data = file_get_contents($forum_imgs[$i]);
                $file = './imgs/forums/forum'.$uuid. '/' . $fileName . '.jpg';
                $success = file_put_contents($file, $data);

                $stm->execute([':img' => $GLOBALS['url'].'imgs/forums/forum'.$uuid.'/'.$fileName.'.jpg']);
            }
            for ($i = 0; $i < count($content_imgs); $i++) {
                $fileName = $content_imgs[$i]->srcPath;
                $data = file_get_contents($content_imgs[$i]->img);
                $file = './imgs/forums/forum'.$uuid. '/' . $fileName . '.jpg';
                $success = file_put_contents($file, $data);
            }
            $files = glob('./imgs/forums/forum'.$uuid. '/*');
            foreach($files as $file) {
                $img = imagecreatefromstring(file_get_contents($file));
                $stamp = imagecreatefrompng('./src/routes/assets/stamp.png');
            
                $marge_right = 10;
                $marge_bottom = 10;
                $sx = imagesx($stamp);
                $sy = imagesy($stamp);
            
                imagecopy($img, $stamp, imagesx($img) - $sx - $marge_right, imagesy($img) - $sy - $marge_bottom, 0, 0, imagesx($stamp), imagesy($stamp));
            
                imagejpeg($img,$file);
                imagedestroy($img);
                imagedestroy($stamp);
            }
            $db = null;

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
        try{
            $db = new db();
            $db = $db->connect();
            for($i = 0; $i < count($id); $i++) {
                $sql = "DELETE FROM forums WHERE id = '$id[$i]'";
                $stmt = $db->prepare($sql);
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


$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function($req, $res) {
    $handler = $this->notFoundHandler; // handle using the default Slim page not found handler
    return $handler($req, $res);
});

