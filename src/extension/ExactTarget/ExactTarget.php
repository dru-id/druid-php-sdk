<?php namespace Genetsis\Extension;

use Genetsis\Identity;
use Genetsis\core\OAuthConfig;

class ExactTarget
{

    protected static $et_client;

    private static $DEV_SUFFIX = "_dev";
    private static $devMode = "_dev";

    private static $MASTER_TABLE = "Local_MasterActivity_SPA";
    private static $PARTICIPATION_TABLE = "DE_Consumer_Participation";
    private static $EVALUATION_TABLE = "Activity_Evaluation_party";
    private static $QUESTIONAIRE_TABLE = "Answers_vs_Consumer";

    private static $initialized = false;

    private static $et_config = array(
        'appsignature' => 'none',
        'defaultwsdl' => 'https://webservice.exacttarget.com/etframework.wsdl',
        'xmlloc' => __DIR__ . '/ExactTargetWSDL.xml',
    );

    public static function init(array $params = null, $devMode = false)
    {
        try {

            if (isset($params['sync'])) {
                Identity::initConfig();
            } else {
                Identity::init();
            }

            self::$devMode = $devMode;
            self::$et_client = new \ET_Client(false, false, array_merge(self::$et_config, $params));

            self::$initialized = true;

        } catch (\Exception $e) {
            var_dump($e, 'error', __METHOD__, __LINE__);
        }
    }

    private static function check()
    {
        if (!self::$initialized) {
            throw new Exception("Exactarget module is not initialized correctly. Please call ExactTarget::init(...) method");
        }
    }

    public static function activity(
        ActivityType $act_type,
        $city = null,
        $postalCode = null,
        $contactPerson = null,
        $contactEmail = null,
        $venueName = null,
        $address = null)
    {

        self::check();


        $extra = array();

        if ($city != null) {
            $extra["City"] = $city;
        }

        if ($postalCode != null) {
            $extra["PostalCode"] = $postalCode;
        }

        if ($contactPerson != null) {
            $extra["ContactPerson"] = $contactPerson;
        }

        if ($contactEmail != null) {
            $extra["EmailContact"] = $contactEmail;
        }

        if ($venueName != null) {
            $extra["VenueName"] = $venueName;
        }

        if ($address != null) {
            $extra["Address"] = $address;
        }

        $DRRow = self::buildActivityDER($act_type, extra);

        $result = $DRRow->post();
        self::checkResult($result);

    }


    public static function participate(
        ActivityType $act_type,
        $oid = null,
        $url = null,
        $thumbnail = null)
    {

        self::check();

        $extra = array();

        if ($oid != null) {
            $extra["Object_Id"] = $oid;
        }

        if ($url != null) {
            $extra["URL"] = $url;
        }

        if ($thumbnail != null) {
            $extra["URLThumbnail"] = $thumbnail;
        }

        $DRRow = self::buildParticipationDER($act_type);

        $result = $DRRow->post();
        self::checkResult($result);

    }

    public static function evaluate(
        ActivityType $act_type,
        $capacity = null,
        $womenpc = null,
        $menpc = null,
        $afinity = null,
        $priceBottle = null,
        $boughBottles = null,
        $tastings = null,
        $bottlesLeft = null,
        $party = null,
        $material = null,
        $observations = null,
        $numCapacity = null,
        $startedOn = null,
        $finishedOn = null)
    {

        self::check();


        $extra = array();

        if ($capacity != null) {
            $extra["Capacity"] = $capacity;
        }

        if ($womenpc != null) {
            $extra["Women"] = $womenpc;
        }

        if ($menpc != null) {
            $extra["Men"] = $menpc;
        }

        if ($afinity != null) {
            $extra["Afinity"] = $afinity;
        }

        $extra["Currency"] = "EUR";

        if ($priceBottle != null) {
            $extra["PriceBottle"] = $priceBottle;
        }

        if ($boughBottles != null) {
            $extra["BoughBottles"] = $boughBottles;
        }

        if ($tastings != null) {
            $extra["Tastings"] = $tastings;
        }

        if ($bottlesLeft != null) {
            $extra["BottlesLeft"] = $bottlesLeft;
        }

        if ($party != null) {
            $extra["Party"] = $party;
        }

        if ($material != null) {
            $extra["Material"] = $material;
        }

        if ($observations != null) {
            $extra["Observations"] = $observations;
        }

        if ($numCapacity != null) {
            $extra["Num_capacity"] = $numCapacity;
        }

        if ($startedOn != null) {
            $extra["DateHourStart"] = $startedOn;
        }

        if ($finishedOn != null) {
            $extra["DateHourEnd"] = $finishedOn;
        }

        $DRRow = self::buildEvaluationDER($act_type, $extra);

        $result = $DRRow->post();
        self::checkResult($result);

    }

    private static function checkResult($result)
    {
        if (!$result->status) {
            throw new \Exception("Error posting to exactTarget. Maybe api is down");
        } else if ($result->code != 200) {
            throw new \Exception("Operation failed: " + $result->message);
        }
    }

    private static function buildActivityDER(ActivityType $act_type, array $params)
    {

        $act_name = OAuthConfig::getAppName();
        $act_brand = OAuthConfig::getBrand();
        $act_date = (new \DateTime())->format('m-d-Y H:i:s');

        $DRRow = new \ET_DataExtension_Row();
        $DRRow->authStub = self::$et_client;
        $DRRow->props = array_merge(
            array(
                "ActivityName" => $act_name,
                "ActivityStartDate" => $act_date,
                "Country" => "Spain",
                "Brand" => $act_brand,
                "ActivityType" => $act_type,
                "LegalDisclaimerId" => "4",
                "ActivityId" => $act_date . "-" . $act_brand . "-" . $act_type . "-" . $act_name,
                "ModETDate" => $act_date
            ), $params);

        $DRRow->Name = getTable(self::$MASTER_TABLE);

        return $DRRow;
    }

    private static function buildParticipationDER(ActivityType $act_type, array $params)
    {

        $act_name = OAuthConfig::getAppName();
        $act_brand = OAuthConfig::getBrand();
        $act_date = (new \DateTime())->format('m-d-Y H:i:s');

        $DRRow = new \ET_DataExtension_Row();
        $DRRow->authStub = self::$et_client;
        $DRRow->props = array_merge(
            array(
                "CreatedOn" => $act_date,
                "ActivityID" => $act_date . "-" . $act_brand . "-" . $act_type . "-" . $act_name,
            ), $params);
        $DRRow->Name = getTable(self::$PARTICIPATION_TABLE);

        return $DRRow;
    }

    private static function buildEvaluationDER(ActivityType $act_type, array $params)
    {

        $act_name = OAuthConfig::getAppName();
        $act_brand = OAuthConfig::getBrand();
        $act_date = (new \DateTime())->format('m-d-Y H:i:s');

        $DRRow = new \ET_DataExtension_Row();
        $DRRow->authStub = self::$et_client;
        $DRRow->props = array_merge(
            array(
                "ActivityID" => $act_date . "-" . $act_brand . "-" . $act_type . "-" . $act_name,
                "ModETDate" => $act_date
            ), $params);
        $DRRow->Name = getTable(self::$EVALUATION_TABLE);
    }

    private static function getTable($tblName)
    {
        return $tblName . (self::$devMode ? self::$DEV_SUFFIX : '');
    }
}