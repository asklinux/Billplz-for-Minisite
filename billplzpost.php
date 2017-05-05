<?php

require_once 'billplz.php';
require_once 'configuration.php';

class billplzpost {

    var $variable;
    var $billplz;

    function __construct() {

        $this->variable = array();
        $this->billplz = new billplz;
    }

    function apikey() {
        global $api_key;
        if ($api_key == 'APIKEY') {
            echo('You need to set up your API Key');
        } else {
            $this->variable['api_key'] = $api_key;
        }
        return $this;
    }

    function collection() {
        global $collection_id;

        if (isset($_POST['collection_id'])) {
            $this->variable['collection_id'] = $_POST['collection_id'];
        } else {
            $this->variable['collection_id'] = $collection_id;
        }
        return $this;
    }

    function name() {
        if (isset($_POST['nama'])) {
            $this->variable['name'] = filter_var($_POST['nama'], FILTER_SANITIZE_STRING);
        } else {
            $this->variable['name'] = 'No Name';
            echo('You need to pass the parameter "nama"');
        }
        return $this;
    }

    function email() {
        if (isset($_POST['email'])) {
            $this->variable['email'] = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
            if (!filter_var($this->variable['email'], FILTER_VALIDATE_EMAIL) === false) {
                //Nothing to do
            } else {
                echo($_POST['email'] . 'is not a valid email address');
            }
        } else {
            $this->variable['email'] = '';
        }
        return $this;
    }

    function mobile() {
        if (isset($_POST['telefonbimbit'])) {
            $this->variable['mobile'] = filter_var($_POST['telefonbimbit'], FILTER_SANITIZE_STRING);
        } else {
            if ($this->variable['email'] = '') {
                echo('You need to pass the parameter "telefonbimbit"');
            }
        }
        return $this;
    }

    function amount() {

        global $amount;
        if ($amount == '') {
            if (isset($_POST['amaun'])) {
                $this->variable['amount'] = filter_var($_POST['amaun'], FILTER_SANITIZE_STRING);
            } else {
                $this->variable['amount'] = '2';
                echo('You need to pass the parameter "amaun"');
            }
        } else {
            $this->variable['amount'] = $amount;
        }
        return $this;
    }

    function deliver() {
        if (isset($_POST['notifikasi'])) {
            $notification = filter_var($_POST['notifikasi'], FILTER_SANITIZE_STRING);
            if ($notification == 'ya') {
                $this->variable['notifikasi'] = '3';
            } else if ($notification == 'email') {
                $this->variable['notifikasi'] = '1';
            } else { //SMS
                $this->variable['notifikasi'] = '2';
            }
        } else {
            $this->variable['notifikasi'] = '0';
        }
        return $this;
    }

    function reference_label_1() {
        if (isset($_POST['reference_label_1'])) {
            $this->variable['reference_label_1'] = filter_var($_POST['reference_label_1'], FILTER_SANITIZE_STRING);
        } else {
            $this->variable['reference_label_1'] = 'ID';
        }
        return $this;
    }

    function reference_1() {
        if (isset($_POST['reference_1'])) {
            $this->variable['reference_1'] = filter_var($_POST['reference_1'], FILTER_SANITIZE_STRING);
        } else {
            $this->variable['reference_1'] = '';
        }
        return $this;
    }

    function reference_label_2() {
        if (isset($_POST['reference_label_2'])) {
            $this->variable['reference_label_2'] = filter_var($_POST['reference_label_2'], FILTER_SANITIZE_STRING);
        } else {
            $this->variable['reference_label_2'] = 'ID';
        }
        return $this;
    }

    function reference_2() {
        if (isset($_POST['reference_2'])) {
            $this->variable['reference_2'] = filter_var($_POST['reference_2'], FILTER_SANITIZE_STRING);
        } else {
            $this->variable['reference_2'] = '';
        }
        return $this;
    }

    function description() {
        if (isset($_POST['description'])) {
            $this->variable['description'] = filter_var($_POST['description'], FILTER_SANITIZE_STRING);
        } else {
            $this->variable['description'] = 'No Description Provided';
            echo('You need to pass the parameter "description"');
        }
        return $this;
    }

    function redirect() {
        global $websiteurl;
        $this->variable['redirect_url'] = $websiteurl . 'verifytrans.php';
        return $this;
    }

    function callback() {
        global $websiteurl;
        $this->variable['callback_url'] = $websiteurl . 'callback.php';
        return $this;
    }

    function overrideSuccessPath() {
        if (isset($_POST['successpath'])) {
            $this->variable['redirect_url'] = $this->variable['redirect_url'] . '?successpath=' . base64_encode(filter_var($_POST['successpath'], FILTER_SANITIZE_STRING));
        } else {
            //Do Nothing
        }
        return $this;
    }

    function process() {
        global $websiteurl;
        global $fallbackurl;
        $this->billplz->setAmount($this->variable['amount'])
                ->setCollection($this->variable['collection_id'])
                ->setDeliver($this->variable['notifikasi'])
                ->setDescription($this->variable['description'])
                ->setEmail($this->variable['email'])
                ->setMobile($this->variable['mobile'])
                ->setName($this->variable['name'])
                ->setPassbackURL($this->variable['callback_url'], $this->variable['redirect_url'])
                ->setReference_1($this->variable['reference_1'])
                ->setReference_1_Label($this->variable['reference_label_1'])
                ->setReference_2($this->variable['reference_2'])
                ->setReference_2_Label($this->variable['reference_label_2'])
                ->create_bill($this->variable['api_key'], true);

        //If the Create Bills API NOT Successfully triggered
        if ($this->billplz->getURL() == '') {
            //If you have set the fallback url if the Create Bill API failed
            if ($fallbackurl != '') {
                echo "<script>location.href = '" . $fallbackurl . "'</script>";
            }
            //If you have'nt set the fallback url, user will redirected to website url if Create Bill API failed
            else {
                echo "<script>location.href = '" . $websiteurl . "'</script>";
            }
        }
        //If the Create Bills API Successfully triggered
        else {
            header('Location: ' . $this->billplz->getURL());
        }
    }

}

$call = new billplzpost;
$call
        ->apikey()
        ->collection()
        ->name()
        ->email()
        ->mobile()
        ->amount()
        ->deliver()
        ->reference_label_1()
        ->reference_1()
        ->reference_label_2()
        ->reference_2()
        ->description()
        ->redirect()
        ->overrideSuccessPath()
        ->callback();
//////////////////////////////////////////////////
// Include tracking code here
//////////////////////////////////////////////////
$call->process();
