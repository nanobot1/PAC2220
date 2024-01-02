<?
require_once(__DIR__ . "./../libs/BGETechTraits.php");  // diverse Klassen

class PAC2200 extends IPSModule
{

    use Semaphore, VariableProfile;
    const PREFIX = 'PAC2200';
    const Swap = true;
    public static $Variables = [
        ['Frequency', VARIABLETYPE_FLOAT, 'Hertz.50', 55, 3, 2, true],
        ['Reactive power average reference', VARIABLETYPE_FLOAT, 'VaR', 503, 3, 2, true],
        ['Reactive power average delivery', VARIABLETYPE_FLOAT, 'VaR', 507, 3, 2, true],
        ['Active power average reference', VARIABLETYPE_FLOAT, 'PAC22200.KW', 501, 3, 2, true],
        ['Active power average delivery', VARIABLETYPE_FLOAT, 'PAC22200.KW', 505, 3, 2, true],
        ['Reactive power L1', VARIABLETYPE_FLOAT, 'VaR', 31, 3, 2, true],
        ['Reactive power L2', VARIABLETYPE_FLOAT, 'VaR', 33, 3, 2, true],
        ['Reactive power L3', VARIABLETYPE_FLOAT, 'VaR', 35, 3, 2, true],
        ['apparent power L1', VARIABLETYPE_FLOAT, 'VA', 19, 3, 2, true],
        ['apparent power L2', VARIABLETYPE_FLOAT, 'VA', 21, 3, 2, true],
        ['apparent power L3', VARIABLETYPE_FLOAT, 'VA', 23, 3, 2, true],
        ['active power L1', VARIABLETYPE_FLOAT, 'PAC22200.KW', 25, 3, 2, true],
        ['active power L2', VARIABLETYPE_FLOAT, 'PAC22200.KW', 27, 3, 2, true],
        ['active power L3', VARIABLETYPE_FLOAT, 'PAC22200.KW', 29, 3, 2, true],
        ['Total power factor', VARIABLETYPE_FLOAT, '', 69, 3, 2, true],
        ['Power factor L1', VARIABLETYPE_FLOAT, '', 37, 3, 2, true],
        ['Power factor L2', VARIABLETYPE_FLOAT, '', 39, 3, 2, true],
        ['Power factor L3', VARIABLETYPE_FLOAT, '', 41, 3, 2, true],
        ['Total reactive power', VARIABLETYPE_FLOAT, 'VaR', 67, 3, 2, true],
        ['Total apparent power', VARIABLETYPE_FLOAT, 'VA', 63, 3, 2, true],
        ['Total active power', VARIABLETYPE_FLOAT, 'PAC22200.KW', 65, 3, 2, true],
        ['Max reactive power', VARIABLETYPE_FLOAT, 'VaR', 513, 3, 2, true],
        ['Min reactive power', VARIABLETYPE_FLOAT, 'VaR', 515, 3, 2, true],
        ['Min active power', VARIABLETYPE_FLOAT, 'PAC22200.KW', 511, 3, 2, true],
        ['Max active power', VARIABLETYPE_FLOAT, 'PAC22200.KW', 509, 3, 2, true],
        ['Voltage L1 L2', VARIABLETYPE_FLOAT, 'Volt.230', 7, 3, 2, true],
        ['Voltage L1 L3', VARIABLETYPE_FLOAT, 'Volt.230', 9, 3, 2, true],
        ['Voltage L3 L1', VARIABLETYPE_FLOAT, 'Volt.230', 11, 3, 2, true],
        ['Voltage L1 N', VARIABLETYPE_FLOAT, 'Volt.230', 1, 3, 2, true],
        ['Voltage L2 N', VARIABLETYPE_FLOAT, 'Volt.230', 3, 3, 2, true],
        ['Voltage L3 N', VARIABLETYPE_FLOAT, 'Volt.230', 5, 3, 2, true],
        ['Current L1', VARIABLETYPE_FLOAT, 'Ampere', 13, 3, 2, true],
        ['Current L2', VARIABLETYPE_FLOAT, 'Ampere', 15, 3, 2, true],
        ['Current L3', VARIABLETYPE_FLOAT, 'Ampere', 17, 3, 2, true],
        ['Active work reference tariff 1', VARIABLETYPE_FLOAT, 'PAC22200.myKWH', 801, 3, 4, true],
        ['Active work reference tariff 2', VARIABLETYPE_FLOAT, 'PAC22200.myKWH', 805, 3, 4, true]
    ];

    public function __construct($InstanceID)
    {
        //Never delete this line!
        parent::__construct($InstanceID);
    }

    // Überschreibt die interne IPS_Create($id) Funktion
    public function Create()
    {
        // Diese Zeile nicht löschen.
        parent::Create();

        $this->ConnectParent("{A5F663AB-C400-4FE5-B207-4D67CC030564}"); //ModBus Gateway
        $this->RegisterPropertyInteger('Interval', 0);
        $this->RegisterPropertyBoolean('Active', true);
        $Variables = [];
        foreach (static::$Variables as $Pos => $Variable) {
            $Variables[] = [
                'Ident'    => str_replace(' ', '', $Variable[0]),
                'Name'     => $this->Translate($Variable[0]),
                'VarType'  => $Variable[1],
                'Profile'  => $Variable[2],
                'Address'  => $Variable[3],
                'Function' => $Variable[4],
                'Quantity' => $Variable[5],
                'Pos'      => $Pos + 1,
                'Keep'     => $Variable[6]
            ];
        }
        $this->RegisterPropertyString('Variables', json_encode($Variables));
        $this->RegisterTimer('UpdateTimer', 0, static::PREFIX . '_RequestRead($_IPS["TARGET"]);');

    }

    // Überschreibt die intere IPS_ApplyChanges($id) Funktion
    public function ApplyChanges()
    {
        // Diese Zeile nicht löschen
        parent::ApplyChanges();

        $this->RegisterProfileFloat('VaR', '', '', ' VAr', 0, 0, 0, 2);
        $this->RegisterProfileFloat('VA', '', '', ' VA', 0, 0, 0, 2);
        $this->RegisterProfileFloat('PhaseAngle', '', '', ' °', 0, 0, 0, 2);
        $this->RegisterProfileFloat('Intensity.F', '', '', ' %', 0, 100, 0, 2);
        $this->RegisterProfileFloat('kVArh', '', '', ' kVArh', 0, 100, 0, 2);
        $this->RegisterProfileInteger('Volt.I', 'Electricity', '', ' V', 0, 0, 0);
        $this->RegisterProfileInteger('Hertz.I', 'Electricity', '', ' Hz', 0, 0, 0);
        $this->RegisterProfileInteger('Ampere.I', 'Electricity', '', ' A', 0, 0, 0);
        $this->RegisterProfileInteger('Watt.I', 'Electricity', '', ' W', 0, 0, 0);
        $this->RegisterProfileInteger('VaR.I', '', '', ' VAr', 0, 0, 0);
        $this->RegisterProfileInteger('VA.I', '', '', ' VA', 0, 0, 0);
        $this->RegisterProfileInteger('Electricity.I', '', '', ' kWh', 0, 0, 0);
        $this->RegisterProfileFloat('PAC22200.myKWH', 'Lightning', '', ' kWh', 0, 0, 0, 2);
        $this->RegisterProfileFloat('PAC22200.KW', 'Electricity', '', ' kW', 0, 0, 0, 2);

        $archiveID = IPS_GetInstanceListByModuleID('{43192F0B-135B-4CE7-A0A7-1475603F3060}')[0];

        $Variables = json_decode($this->ReadPropertyString('Variables'), true);
        foreach ($Variables as $Variable) {
            @$this->MaintainVariable($Variable['Ident'], $Variable['Name'], $Variable['VarType'], $Variable['Profile'], $Variable['Pos'], $Variable['Keep']);
            $this->setArchive($Variable['Name'], $archiveID);
        }
        if ($this->ReadPropertyInteger('Interval') > 0) {
            $this->SetTimerInterval('UpdateTimer', $this->ReadPropertyInteger('Interval'));
        }
        else {
            $this->SetTimerInterval('UpdateTimer', 0);
        }
        if ($this->ReadPropertyBoolean('Active') == false) {
            IPS_SetHidden($this->InstanceID, true);
        }
        else {
            IPS_SetHidden($this->InstanceID, false);
        }
    }

    /**
     * IPS-Instanz Funktion PREFIX_RequestRead.
     * Ließt alle Werte aus dem Gerät.
     *
     * @return bool True wenn Befehl erfolgreich ausgeführt wurde, sonst false.
     */
    public function RequestRead()
    {
        if ($this->ReadPropertyBoolean('Active') != false) {
            $Gateway = IPS_GetInstance($this->InstanceID)['ConnectionID'];
            if ($Gateway == 0) {
                return false;
            }
            $IO = IPS_GetInstance($Gateway)['ConnectionID'];
            if ($IO == 0) {
                return false;
            }
            if (!$this->lock($IO)) {
                return false;
            }
            $Result = $this->ReadData();
            IPS_Sleep(333);
            $this->unlock($IO);
            return $Result;
        }
        return false;

    }

    private function setArchive($variableName, $archiveID)
    {
        $id = IPS_GetVariableIDByName($variableName, $this->InstanceID);
        AC_SetLoggingStatus($archiveID, $id, true);
        if ($variableName == "Wirkarbeit Bezug Tarif 1" || $variableName == "Wirkarbeit Bezug Tarif 2") {
            AC_SetAggregationType($archiveID, $id, 1); //0-Standard / 1-Zähler
        }
    }

    private function ReadData()
    {
        $Variables = json_decode($this->ReadPropertyString('Variables'), true);
        foreach ($Variables as $Variable) {
            if (!$Variable['Keep']) {
                continue;
            }
            $SendData['DataID'] = '{E310B701-4AE7-458E-B618-EC13A1A6F6A8}'; //GUID aus ParentRequirements
            $SendData['Function'] = $Variable['Function'];
            $SendData['Address'] = $Variable['Address'];
            $SendData['Quantity'] = $Variable['Quantity'];
            $SendData['Buffer'] = 'Test';
            $SendData['Data'] = '';
            set_error_handler([$this, 'ModuleErrorHandler']);
            $ReadData = $this->SendDataToParent(json_encode($SendData));

            restore_error_handler();
            if ($ReadData === false) {
                return false;
            }
            $ReadValue = substr($ReadData, 2);
            $this->SendDebug($Variable['Name'] . ' RAW', $ReadValue, 1);


            if (static::Swap) {
                $ReadValue = strrev($ReadValue);
            }


            $Value = $this->ConvertValue($Variable, $ReadValue);


            if ($Value === null) {
                $this->LogMessage(sprintf($this->Translate('Combination of type and size of value (%s) not supported.'), $Variable['Name']), KL_ERROR);
                continue;
            }

            if ($Variable['Quantity'] == 4 || $Variable['Profile'] == 'PAC22200.KW') {
                $Value = (floatval($Value) / 1000);
            }
            $this->SendDebug($Variable['Name'], $Value, 0);
            $this->SetValueExt($Variable, $Value);
        }
        return true;
    }

    protected function ModuleErrorHandler($errno, $errstr)
    {
        $this->SendDebug('ERROR', utf8_decode($errstr), 0);
        echo $errstr;
    }

    private function ConvertValue(array $Variable, string $Value)
    {
        switch ($Variable['VarType']) {
            case VARIABLETYPE_BOOLEAN:
                if ($Variable['Quantity'] == 1) {
                    return ord($Value) == 0x01;
                }
                break;
            case VARIABLETYPE_INTEGER:
                switch ($Variable['Quantity']) {
                    case 1:
                        return ord($Value);
                    case 2:
                        return unpack('n', $Value)[1];
                    case 4:
                        return unpack('N', $Value)[1];
                    case 8:
                        return unpack('J', $Value)[1];
                }
                break;
            case VARIABLETYPE_FLOAT:
                switch ($Variable['Quantity']) {
                    case 4:
                        return unpack('d', $Value)[1];
                    case 8:
                    case 2:
                        return unpack('f', $Value)[1];
                }
                break;
            case VARIABLETYPE_STRING:
                return $Value;
        }
        return null;
    }

    /**
     * Setzte eine IPS-Variableauf den Wert von $value.
     *
     * @param array $Variable Statusvariable
     * @param mixed $Value Neuer Wert der Statusvariable.
     */
    protected function SetValueExt($Variable, $Value)
    {
        $id = @$this->GetIDForIdent($Variable['Ident']);
        if ($id == false) {
            $this->MaintainVariable($Variable['Ident'], $Variable['Name'], $Variable['VarType'], $Variable['Profile'], $Variable['Pos'], $Variable['Keep']);
        }
        if (method_exists('IPSModule', 'SetValue')) {
            parent::SetValue($Variable['Ident'], $Value);
        }
        else {
            $id = @$this->GetIDForIdent($Variable['Ident']);
            SetValueFloat($id, $Value);
        }
        return true;
    }

    public function GetConfigurationForm()
    {
        $Form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
        $Form['actions'][0]['onClick'] = static::PREFIX . '_RequestRead($id)';
        if (count(static::$Variables) == 1) {
            unset($Form['elements'][1]);
        }
        //$this->SendDebug('form', json_encode($Form), 0);
        return json_encode($Form);
    }


}
