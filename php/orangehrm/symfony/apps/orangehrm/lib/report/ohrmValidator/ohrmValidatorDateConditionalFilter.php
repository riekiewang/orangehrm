<?php

class ohrmValidatorDateConditionalFilter extends ohrmValidatorConditionalFilter {

    protected function configure($options = array(), $messages = array()) {
        parent::configure($options, $messages);
         $inputDatePattern = sfContext::getInstance()->getUser()->getDateFormat();
        
        $this->addOption('values', array('from', 'to'));
        $this->addMessage('value1_required', 'Date value required');
        $this->addMessage('value2_required', 'Second date required');
        $this->addMessage('value1_value2_required', 'Both date values required');
        $this->addMessage('value1_greater_than_value2', 'Second date should be after or equal to first date');
        $this->addMessage('value1_invalid', 'Date does not match the date format '.get_datepicker_date_format($inputDatePattern));
        $this->addMessage('value2_invalid', 'Date does not match the date format '.get_datepicker_date_format($inputDatePattern));
        $this->addMessage('value1_and_value2_invalid', 'Please enter valid dates in format of '.get_datepicker_date_format($inputDatePattern));
        
    }
    
    protected function isValid($value) {
        $inputDatePattern = sfContext::getInstance()->getUser()->getDateFormat();
        $localizationService = new LocalizationService();
        $result = $localizationService->convertPHPFormatDateToISOFormatDate($inputDatePattern, $value);
        return ($result == "Invalid date") ? false : $result;
    }

    protected function validatedBetween($value1, $value2) {
        $valid = false;
        $date1 = $this->getDate($this->isValid($value1));
        $date2 = $this->getDate($this->isValid($value2));
        if (!empty($date1) && !empty($date2)) {
            $valid = $date2 >= $date1;
        }
        return $valid;
    }
    
    protected function getDate($value) {
        $dateTime = null;
        $valid = false;

        $trimmedValue = trim($value);
        $pattern = 'yyyy-MM-dd';
        
        // check date format

        if (is_string($value) && !empty($pattern)) {

            $dateFormat = new sfDateFormat();

            try {
                $dateParts = $dateFormat->getDate($trimmedValue, $pattern);

                if (is_array($dateParts) && isset($dateParts['year']) && isset($dateParts['mon']) && isset($dateParts['mday'])) {

                    $day = $dateParts['mday'];
                    $month = $dateParts['mon'];
                    $year = $dateParts['year'];

                    // Additional check done for 3 digit years, or more than 4 digit years
                    if (checkdate($month, $day, $year) && ($year >= 1000) && ($year <= 9999) ) {
                        $dateTime = new DateTime();
                        $dateTime->setTimezone(new DateTimeZone(date_default_timezone_get()));
                        $dateTime->setDate($year, $month, $day);

                        $valid = true;
                    }
                }


            } catch (Exception $e) {
                $valid = false;
            }
        }
        return($dateTime);        
    }
    
}

