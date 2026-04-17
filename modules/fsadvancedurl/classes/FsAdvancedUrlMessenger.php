<?php
/**
 * Copyright 2022 ModuleFactory
 *
 * @author    ModuleFactory
 * @copyright ModuleFactory all rights reserved.
 * @license   https://www.apache.org/licenses/LICENSE-2.0
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
class FsAdvancedUrlMessenger
{
    private static $messages = [];
    private static $readed_from_file = false;
    private static $module_name = 'fsadvancedurl';
    private static $messages_file = 'messages.json';

    public static function getMessagesHtml()
    {
        return self::getErrorMessages(true) . self::getSuccessMessages(true);
    }

    public static function addSuccessMessage($message)
    {
        self::addMessage('success', $message);
    }

    public static function getSuccessMessages($html = false)
    {
        $return_messages = [];

        self::readFromFile();

        if (self::$messages) {
            foreach (self::$messages as $message) {
                if ($message['type'] == 'success') {
                    $return_messages[] = $message['message'];
                }
            }
        }

        if ($html) {
            if ($return_messages) {
                $module = Module::getInstanceByName(self::$module_name);

                return $module->displayConfirmation(implode('<br />', $return_messages));
            }

            return '';
        }

        return $return_messages;
    }

    public static function addErrorMessage($message)
    {
        self::addMessage('error', $message);
    }

    public static function getErrorMessages($html = false)
    {
        $return_messages = [];

        self::readFromFile();

        if (self::$messages) {
            foreach (self::$messages as $message) {
                if ($message['type'] == 'error') {
                    $return_messages[] = $message['message'];
                }
            }
        }

        if ($html) {
            if ($return_messages) {
                $module = Module::getInstanceByName(self::$module_name);
                if (count($return_messages) < 2) {
                    $return_messages = implode('', $return_messages);
                }

                return $module->displayError($return_messages);
            }

            return '';
        }

        return $return_messages;
    }

    private static function addMessage($type, $message)
    {
        self::$messages[] = ['type' => $type, 'message' => $message];
        self::saveToFile();
    }

    private static function readFromFile()
    {
        $messages_file = _PS_MODULE_DIR_ . self::$module_name . '/' . self::$messages_file;
        if (!self::$readed_from_file) {
            if (file_exists($messages_file)) {
                self::$messages = FsAdvancedUrlModule::jsonDecodeStatic(Tools::file_get_contents($messages_file), true);
                unlink($messages_file);
            }

            self::$readed_from_file = true;
        }
    }

    private static function saveToFile()
    {
        $messages_file = _PS_MODULE_DIR_ . self::$module_name . '/' . self::$messages_file;
        $file = fopen($messages_file, 'w');
        fwrite($file, FsAdvancedUrlModule::jsonEncodeStatic(self::$messages));
        fclose($file);
    }
}
