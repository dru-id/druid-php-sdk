<?php namespace Genetsis\extension\ExactTarget;

use Genetsis\Identity;
use Genetsis\core\OAuthConfig;
use Genetsis\UserApi;

/**
 * Class ExactTarget
 * Simple ExacTarget wrapper. This wrapper uses Genetsis\Identity library. eg Brand and activity name are obtained from there
 * @package Genetsis\extension\ExactTarget
 */
class ExactTarget
{

    protected static $et_client;

    private static $DEV_SUFFIX = "_dev";
    private static $devMode;

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

    /**
     * Initalize library
     *
     * @param array $params Initialization params needed for underlying ET_Client class
     * @param bool $devMode if devMode active, all operation will be donde in *_dev tables from ET
     */
    public static function init(array $params = array(), $devMode = false)
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

    /**
     * Add an activity to ET
     *
     * @param string type of the activity (@see ActivityType constants)
     * @param string $city city where activity happened
     * @param string $postalCode postal code where activity happened
     * @param string $contactPerson contact name of person responsible of the activity
     * @param string $contactEmail email of person responsible of the activity
     * @param string $venueName name of physical site where activity happened
     * @param string $address addrees where activity happened
     */
    public static function activity(
        $act_type,
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

        $DRRow = self::buildActivityDER($act_type, $extra);

        $result = $DRRow->post();
        self::checkResult($result);

    }

    public static function evaluate(
        $act_type,
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

    /**
     * @param string type of the activity (@see ActivityType constants)
     * @param string $url
     * @param string $thumbnail
     * @param string $oid objectId of user. if this parameter is not defined or is null, logged user will be used
     */
    public static function participate(
        $act_type,
        $url = null,
        $thumbnail = null,
        $oid = null)
    {

        self::check();

        $extra = array();

        if ($url != null) {
            $extra["URL"] = $url;
        }

        if ($thumbnail != null) {
            $extra["URLThumbnail"] = $thumbnail;
        }

        $extra["Object_Id"] = $oid == null ? UserApi::getUserLoggedOid() : $oid;

        $DRRow = self::buildParticipationDER($act_type);

        $result = $DRRow->post();
        self::checkResult($result);

    }

    /**
     * @param string type of the activity (@see ActivityType constants)
     * @param String $question_id
     * @param String $answer_id
     * @param string $consumer_email email of user. if this parameter is not defined or is null, logged user will be used
     */
    public static function poll(
        $act_type,
        $question_id,
        $answer_id,
        $consumer_email = null)
    {

        self::check();

        $extra = array();


        $extra["IdQuestionAnswer"] = $question_id;

        $extra["IdAnswer"] = $answer_id;

        $extra["EmailAddress"] = $consumer_email == null ? UserApi::getUserLoggedOid() : $consumer_email;

        $DRRow = self::buildQuestionaireDER($act_type);

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

    private static function buildActivityDER($act_type, array $params)
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

        $DRRow->Name = self::getTable(self::$MASTER_TABLE);

        return $DRRow;
    }

    private static function buildParticipationDER($act_type, array $params)
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
        $DRRow->Name = self::getTable(self::$PARTICIPATION_TABLE);

        return $DRRow;
    }

    private static function buildEvaluationDER($act_type, array $params)
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
        $DRRow->Name = self::getTable(self::$EVALUATION_TABLE);

        return $DRRow;
    }

    private static function buildQuestionaireDER($act_type, array $params)
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
        $DRRow->Name = self::getTable(self::$QUESTIONAIRE_TABLE);
    }

    private static function getTable($tblName)
    {
        return $tblName . (self::$devMode ? self::$DEV_SUFFIX : '');
    }
}