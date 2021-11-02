<?php

namespace Hackzilla\PasswordGenerator\Generator;

use Hackzilla\PasswordGenerator\Exception\FileNotFoundException;
use Hackzilla\PasswordGenerator\Exception\WordsNotFoundException;
use Hackzilla\PasswordGenerator\Model\Option\Option;
use InvalidArgumentException;

class DicewarePasswordGenerator extends AbstractPasswordGenerator
{
    const OPTION_WORDS = 'WORDS';
    const OPTION_DICE = 'DICE';
    const OPTION_REQUIRE_UPPERCASE = 'UPPERCASE';

    const PARAMETER_DICTIONARY_FILE = 'DICTIONARY';
    const PARAMETER_WORD_CACHE = 'CACHE';
    const PARAMETER_SEPARATOR = 'SEPARATOR';

    public function __construct()
    {
        $this
            ->setOption(self::OPTION_WORDS, ['type' => Option::TYPE_INTEGER, 'default' => 4])
            ->setOption(self::OPTION_DICE, ['type' => Option::TYPE_INTEGER, 'default' => 5])
            ->setOption(self::OPTION_REQUIRE_UPPERCASE, ['type'=>Option::TYPE_BOOLEAN, 'default'=>false])
            ->setParameter(self::PARAMETER_SEPARATOR, '')
        ;
    }

    /**
     * @inheritDoc
     */
    public function generatePassword()
    {
        $wordList = $this->generateWordList();
        $hasUpperCase = false;

        $words = \count($wordList);

        if (!$words) {
            throw new WordsNotFoundException('No words selected.');
        }

        $password = '';
        $wordCount = $this->getWordCount();

        for ($i = 0; $i < $wordCount; $i++) {
            if ($i) {
                $password .= $this->getWordSeparator();
            }

            $word = $this->getRandomWord();
            if($this->getOptionValue(self::OPTION_REQUIRE_UPPERCASE) && (($i==$wordCount-1 && !$hasUpperCase) || $this->randomInteger(0,1) === 1)){
                $word = ucfirst($word);
                $hasUpperCase = true;
            }
            $password .= $word;
        }

        return $password;
    }

    /**
     * Generate word list for us in generating passwords.
     *
     * @return string[] Words
     *
     * @throws WordsNotFoundException
     */
    public function generateWordList()
    {
        if ($this->getParameter(self::PARAMETER_WORD_CACHE) !== null) {
            return $this->getParameter(self::PARAMETER_WORD_CACHE);
        }

        $lines = explode("\n", file_get_contents($this->getWordList()));
        $words = [];

        foreach ($lines as $line) {
            $arr = explode("\t", $line);
            $words[$arr[0]] = trim($arr[1]);
        }

        if (!$words) {
            throw new WordsNotFoundException('No words selected.');
        }

        $this->setParameter(self::PARAMETER_WORD_CACHE, $words);

        return $words;
    }

    public function getRandomWord()
    {
        $dice = '';
        for ($i = 0; $i < $this->getDice(); $i++) {
            $dice .= $this->randomInteger(1, 6);
        }
        $words = $this->generateWordList();
        return $words[$dice];
    }


    public function getDice()
    {
        return $this->getOptionValue(self::OPTION_DICE);
    }

    public function setDice($dice)
    {
        return $this->setOptionValue(self::OPTION_DICE, $dice);
    }

    /**
     * Get number of words in desired password.
     *
     * @return int
     */
    public function getWordCount()
    {
        return $this->getOptionValue(self::OPTION_WORDS);
    }

    /**
     * Set number of words in desired password(s).
     *
     * @param int $characterCount
     *
     * @return $this
     *
     * @throws InvalidArgumentException
     */
    public function setWordCount($characterCount)
    {
        if (!is_int($characterCount) || $characterCount < 1) {
            throw new InvalidArgumentException('Expected positive integer');
        }

        $this->setOptionValue(self::OPTION_WORDS, $characterCount);

        return $this;
    }

    /**
     * Set word list.
     *
     * @param string $filename
     *
     * @return $this
     *
     * @throws InvalidArgumentException
     * @throws FileNotFoundException
     */
    public function setWordList($filename)
    {
        if (!is_string($filename)) {
            throw new InvalidArgumentException('Expected string');
        } elseif (!file_exists($filename)) {
            throw new FileNotFoundException('File not found');
        }

        $this->setParameter(self::PARAMETER_DICTIONARY_FILE, $filename);
        $this->setParameter(self::PARAMETER_WORD_CACHE, null);

        return $this;
    }

    /**
     * Get word list filename.
     *
     * @return string
     * @throws FileNotFoundException
     *
     */
    public function getWordList()
    {
        if (!file_exists($this->getParameter(self::PARAMETER_DICTIONARY_FILE))) {
            throw new FileNotFoundException();
        }

        return $this->getParameter(self::PARAMETER_DICTIONARY_FILE);
    }

    /**
     * Get word separator.
     *
     * @return string
     */
    public function getWordSeparator()
    {
        return $this->getParameter(self::PARAMETER_SEPARATOR);
    }

    /**
     * Set word separator.
     *
     * @param string $separator
     *
     * @return $this
     *
     * @throws InvalidArgumentException
     */
    public function setWordSeparator($separator)
    {
        if (!is_string($separator)) {
            throw new InvalidArgumentException('Expected string');
        }

        $this->setParameter(self::PARAMETER_SEPARATOR, $separator);

        return $this;
    }

    public function getRequireUppercase()
    {
        return $this->getOption(self::OPTION_REQUIRE_UPPERCASE);
    }

    public function setRequireUppercase(bool $require)
    {
        $this->setOptionValue(self::OPTION_REQUIRE_UPPERCASE, $require);

        return $this;
    }
}