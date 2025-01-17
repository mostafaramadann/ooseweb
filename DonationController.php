<?php
require_once("DonationView.php");
require_once("DonationModel.php");
require_once ("HeaderView.php");
require_once ("UserModel.php");
require_once ("cashDonate.php");
require_once ("visaDonate.php");
if(session_status()==PHP_SESSION_NONE)
session_start();
$hview =new HeaderView();
$um = UserModel::MakeObject();
if(isset($_SESSION['loggedin']))
{
    $um->Retrieveuser(null,null,$_SESSION['id'],2);
$dview = new DonationView();
$dmodel = new DonationModel();
$dmodel->attach($um);
$hview->showView($um->getOtherlinks());
$dview->showView(null);

////////////////// Strategy Design Pattern and Validating//////////////////////////////////////////
if(isset($_POST['submit'])) {
        if (isset($_POST["visa"]) && !isset($_POST["cash"])) {
            if (preg_match("#^4[0-9]{12}(?:[0-9]{3})?$#", $_POST['cardnumber'])
                && $_POST['expm'] != "" && $_POST['expy'] != "" &&
                $_POST['visaamount'] != "" && (int)$_POST['visaamount'] > 0
                && $_POST['CVC'] != "" && preg_match("#^[0-9]{3,4}$#", $_POST['CVC'])) {
                $v = new VisaDonate();
                $v->pay($_POST['visaamount']);
                $v->setCVC($_POST['CVC']);
                $v->setCreditcardno($_POST['cardnumber']);
                $v->setExpireMonth($_POST['expm']);
                $v->setExpireYear($_POST['expy']);
                echo $v->getAmount();
                $dmodel->setDonationStrategy($v);
                $um->Donate((int)$dmodel->getDonationStrategy()->getAmount(),$dmodel->getDonationStrategy()->text);
                $dmodel->notifyWithLastDonor();
                echo "Donation is Successfull";
        }   else {
                echo "Donation Failed";
            }
    }   elseif (!isset($_POST["visa"]) && isset($_POST["cash"])) {
            if ($_POST['cashamount'] != "" && strlen($_POST['cashamount'] > 0)) {
                $c = new cashDonate();
                $c->pay($_POST['cashamount']);
                $dmodel->setDonationStrategy($c);
                $um->Donate((int)$dmodel->getDonationStrategy()->getAmount(),$dmodel->getDonationStrategy()->text);
                $dmodel->notifyWithLastDonor();
                echo "Donation is successfull";
        } else {
            echo "Donation Failed";
            }
        }
    }
}
else echo "Please Login";
?>
