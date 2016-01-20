<?php

class photo
{
    private $group;
    private $filename;

    public function set_group ($group)
    {
        if (! is_dir(PHOTO_DIR.$group )){
            // directory was not found, aborting
            return false;
        }

        $this->group = $group.'/';
        return true;
    }

    public function set_filename ($filename)
    {
        //uses global defined variable inside config/config.php plus $group plus $filename to form complete filepath/filename
        if (! isset ( $this->group ) ) {
            // variable $group has not been defined, abort
            return false;
        }

        $this->filename = $filename;

        $path_filename = $this->get_photo();

        if ( file_exists( $path_filename ) ) {
            // file exists, cannot set filename!
            $this->filename = '';
            return false;
        }

        return true;
    }

    public function upload ()
    {
        // security check first
        $token_handler = new security();
        $token_handler->check_token();

        // receives data from input form:
        /*
         *  <form action="upload.php" method="post" enctype="multipart/form-data">
                Select image to upload:
                <input type="file" name="fileToUpload" id="fileToUpload">
                <input type="submit" value="Upload Image" name="submit">
            </form>
         */
        $target_dir = PHOTO_DIR;
        $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
        $uploadOk = 1;
        $imageFileType = pathinfo($target_file, PATHINFO_EXTENSION);
        // actual photo/image ?
        $msg = '';
        if(isset($_POST["submit"])) {
            $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
            if($check !== false) {
                $msg .= "File is an image - " . $check["mime"] . ".";
                $uploadOk = 1;
            } else {
                $msg .= "File is not an image.";
                $uploadOk = 0;
            }
        }
        // Check if file already exists
        if (file_exists($target_file)) {
            $msg .= "Sorry, file already exists.";
            $uploadOk = 0;
        }
        // Check file size
        if ($_FILES["fileToUpload"]["size"] > 500000) {
            $msg .= "Sorry, your file is too large.";
            $uploadOk = 0;
        }
        // Allow certain file formats
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
            && $imageFileType != "gif" ) {
            $msg .= "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
        }
        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            $msg .= "Sorry, your file was not uploaded.";
        // if everything is ok, try to upload file
        } else {
            if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
                $msg .= "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";
            } else {
                $msg .= "Sorry, there was an error uploading your file.";
            }
        }
        $result = array ('message' => $msg, 'ok' => $uploadOk);
        return $result;

    }

    public function get_photo ()
    {
        // returns photo's complete path and filename
        if ( isset ( $this->group ) AND isset ( $this->filename ) ) {
            $path_filename = PHOTO_DIR . $this->group . $this->filename;
            if ( ! file_exists ( $path_filename ) ) {
                // file does not exist, error!
                return false;
            }
            return $path_filename;

        } else {
            // can't return due missing data!
            return false;
        }
    }

    public function get_photo_html ()
    {
        $photo_html = "<img src = '" . $this->get_photo() . "' alt = 'photo_id'>";

        return $photo_html;
    }

}