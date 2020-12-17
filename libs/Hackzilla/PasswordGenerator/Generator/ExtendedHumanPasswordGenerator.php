<?php


namespace Hackzilla\PasswordGenerator\Generator;


use Hackzilla\PasswordGenerator\Exception\ImpossiblePasswordLengthException;
use Hackzilla\PasswordGenerator\Exception\NotEnoughWordsException;
use Hackzilla\PasswordGenerator\Exception\WordsNotFoundException;
use Hackzilla\PasswordGenerator\Model\Option\Option;

/**
 * Extends the HumanPasswordGenerator to add the option of requiring one capital letter, by capitalizing at least one word.
 *
 * Class ExtendedHumanPasswordGenerator
 * @package Hackzilla\PasswordGenerator\Generator
 */
class ExtendedHumanPasswordGenerator extends HumanPasswordGenerator
{
    const OPTION_REQUIRE_UPPERCASE = 'UPPERCASE';

    function __construct() {
        parent::__construct();
        $this->setOption(self::OPTION_REQUIRE_UPPERCASE, ['type'=>Option::TYPE_BOOLEAN, 'default'=>false]);
    }

    /**
     * Generate one password based on options.
     *
     * @return string password
     *
     * @throws WordsNotFoundException
     * @throws ImpossiblePasswordLengthException
     * @throws NotEnoughWordsException
     */
    public function generatePassword()
    {
        $wordList = $this->generateWordList();
        $hasUpperCase = false;

        $words = count($wordList);

        if (!$words) {
            throw new WordsNotFoundException('No words selected.');
        }

        $password = '';
        $wordCount = $this->getWordCount();

        if (
            $this->getLength() > 0 &&
            (
                $this->getMinPasswordLength() > $this->getLength()
                ||
                $this->getMaxPasswordLength() < $this->getLength()
            )
        ) {
            throw new ImpossiblePasswordLengthException();
        }

        if (!$this->getLength()) {
            for ($i = 0; $i < $wordCount; $i++) {
                if ($i) {
                    $password .= $this->getWordSeparator();
                }

                $word = $this->randomWord();
                if($this->getOptionValue(self::OPTION_REQUIRE_UPPERCASE) && (($i==$wordCount-1 && !$hasUpperCase) || $this->randomInteger(0,1) === 1)){
                    $word = ucfirst($word);
                    $hasUpperCase = true;
                }
                $password .= $word;
            }

            return $password;
        }

        while(--$wordCount) {
            $thisMin = $this->getLength() - strlen($password) - ($wordCount * $this->getMaxWordLength()) - (strlen($this->getWordSeparator()) * $wordCount);
            $thisMax = $this->getLength() - strlen($password) - ($wordCount * $this->getMinWordLength()) - (strlen($this->getWordSeparator()) * $wordCount);

            if ($thisMin < 1) {
                $thisMin = $this->getMinWordLength();
            }

            if ($thisMax > $this->getMaxWordLength()) {
                $thisMax = $this->getMaxWordLength();
            }

            $length = $this->randomInteger($thisMin, $thisMax);

            $word = $this->randomWord($length, $length);
            if($this->getOptionValue(self::OPTION_REQUIRE_UPPERCASE) && $this->randomInteger(0,1) === 1){
                $word = ucfirst($word);
                $hasUpperCase = true;
            }
            $password .= $word;

            if ($wordCount) {
                $password .= $this->getWordSeparator();
            }
        }

        $desiredLength = $this->getLength() - strlen($password);
        $word = $this->randomWord($desiredLength, $desiredLength);
        if($this->getOptionValue(self::OPTION_REQUIRE_UPPERCASE) && (!$hasUpperCase || $this->randomInteger(0,1) === 1)){
            $word = ucfirst($word);
        }
        $password .= $word;

        return $password;
    }
}