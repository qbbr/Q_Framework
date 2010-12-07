<?php
/**
 * отправка почты
 *
 * <code>
 * $mail = new Q_Mail();
 * $mail->to('to@xz.ru');
 * $mail->from('xz@xz.ru');
 * $mail->subject('Привет');
 * $mail->message('сообщение');
 * $mail->attach('/home/qbbr/asd.png');
 * $mail->send();
 * </code>
 *
 * @author Sokolov Innokenty, <qbbr@qbbr.ru>
 * @copyright Copyright (c) 2010, qbbr
 */

class Q_Mail
{

    /**
     * кодировка письма
     * @var string
     */
    public $charset = "utf8";

    /**
     * кодирование контента
     * @var string
     */
    private $_encoding = "8bit";

    /**
     * формат контента
     * @var string
     */
    private $_contentType = "text/plain";

    /**
     * заголовки
     * @var array
     */
    private $_headers = array();

    /**
     * кому
     * @var string
     */
    private $_to = "";

    /**
     * тема письма
     * @var string
     */
    private $_subject = "";

    /**
     * текст
     * @var string
     */
    private $_message = "";

    /**
     * прикреплённые файлы
     * @var array
     */
    private $_attach = array();

    /**
     * граница для прикреплённых файлов
     * @var string
     */
    private $_boundary = "";


    /**
     * конструктор
     */
    public function __construct()
    {
        $this->_boundary = "--" . md5(uniqid("boundary"));
    }


    /**
     * установка заголовка
     *
     * @access private
     * @param string $key ключ
     * @param string $value значение
     * @return boolean
     */
    private function set_header($key, $value)
    {
        if (empty($key) || empty($value)) return false;

        $this->_headers[$key] = $value;

        return true;
    }


    /**
     * от кого
     *
     * @access public
     * @param string $mail почта
     * @param string $name ФИО
     * @return boolean
     */
    public function from($mail, $name = "")
    {
        return $this->set_header("From", "{$name} <{$mail}>");
    }


    /**
     * обратный адрес
     *
     * @access public
     * @param string $mail почта
     * @param string $name ФИО
     * @return boolean
     */
    public function reply_to($mail, $name = "")
    {
        $mail = preg_replace("/.*<(.*)>/", "$1", $mail);
        return $this->set_header("Reply-To", "{$name} <{$mail}>");
    }


    /**
     * кому
     *
     * @access public
     * @param string $mail почта
     * @return boolean
     */
    public function to($mail)
    {
        $this->_to = $this->parse_mail($mail);
        return true;
    }


    /**
     * открытая копия
     *
     * @access public
     * @param string $mail почта
     * @return boolean
     */
    public function cc($mail)
    {
        return $this->set_header("Cc", $this->parse_mail($mail));
    }


    /**
     * скрытая копия
     *
     * @access public
     * @param string $mail почта
     * @return boolean
     */
    public function bcc($mail)
    {
        return $this->set_header("Bcc", $mail = $this->parse_mail($mail));
    }


    /**
     * тема сообщения
     *
     * @access public
     * @param string $subject тема письма
     * @return boolean
     */
    public function subject($subject)
    {
        $this->_subject = $subject;

        return true;
    }


    /**
     * текст сообщения
     *
     * @access public
     * @param string $msg сообщение
     * @param boolean $content_type_is_html[optional] сообщение в формате html?
     * @return boolean
     */
    public function message($msg, $isHtml = false)
    {
        if ($isHtml) $this->_contentType = "text/html";

        $this->_message = $msg;

        return true;
    }


    /**
     * назначить приоритет важности письма
     *
     * @access public
     * @param int $priority приоритет от 1 (самый высокий) до 5 (самый низкий)
     * @return boolean
     */
    public function priority($priority)
    {
        if (!is_numeric($priority)) return false;

        return $this->set_header("X-Priority", $priority);
    }


    /**
     * прикрепить файл
     *
     * @access public
     * @param string $file путь до файла
     * @param string $file_name[optional] название файла
     * @param string $file_type[optional] тип файла
     * @param string $disposition[optional]
     * @return boolean
     */
    public function attach($file, $fileName = null, $fileType = null, $disposition = "attachment")
    {
        if (!is_file($file)) return false;

        if (empty($fileType)) $fileType = mime_content_type($file); // "application/x-unknown-content-type"
        if (empty($fileName)) $fileName = basename($file);

        $this->_attach[] = array($file, $fileName, $fileType, $disposition);

        return true;
    }


    /**
     * отправка
     *
     * @access public
     * @return boolean
     */
    public function send()
    {
        if (empty($this->_headers)) return false;

        $headers = $this->build_headers();

        $body = empty($this->_attach) ? $this->_message : $this->build_attach();

        return @mail($this->_to, $this->_subject, $body, $headers);
    }


    /**
     * собираем прикреплённые файла
     *
     * @access private
     * @return string
     */
    private function build_attach()
    {
        $body = "This is a multi-part message in MIME format.\n--{$this->_boundary}\n";
        $body .= "Content-Type: {$this->_contentType}; charset={$this->charset}\n";
        $body .= "Content-Transfer-Encoding: {$this->_encoding}\n\n";
        $body .= $this->_message;
        $body .= "\n";

        $attachz = array();

        foreach ($this->_attach as $value) {
            $file = $value[0];
            $basename = $value[1];
            $fileType = $value[2];
            $disposition = $value[3];

            $attachz[] = "--{$this->_boundary}\nContent-type: {$fileType};\n name=\"{$basename}\"\nContent-Transfer-Encoding: base64\nContent-Disposition: {$disposition};\n filename=\"{$basename}\"\n";
            $attachz[] = chunk_split(base64_encode(file_get_contents($file)));
        }

        $body .= implode(chr(13) . chr(10), $attachz);

        return $body;
    }


    /**
     * собираем заголовки
     *
     * @access private
     * @return string
     */
    private function build_headers()
    {
        if (empty($this->_headers["Reply-To"])) {
            $this->reply_to($this->_headers["From"]);
        }

        if (empty($this->_attach)) {
            $this->set_header("Content-Type", "{$this->_contentType}; charset={$this->charset}");
        } else {
            $this->set_header("Content-Type", "multipart/mixed;\n boundary=\"{$this->_boundary}\"");
        }

        $this->set_header("Mime-Version", "1.0");
        $this->set_header("X-Mailer", "PHP " . phpversion());
        $this->set_header("Content-Transfer-Encoding", $this->_encoding);

        $headers = "";
        foreach ($this->_headers as $key => $value) {
            $value = trim($value);
            $headers .= "{$key}: {$value}\n";
        }

        return $headers;
    }

    /**
     * правим почту (запятую), если их несколько
     *
     * @access private
     * @param string $mail электронный адрес
     * @return boolean
     */
    private function parse_mail($mail)
    {
        if (empty($mail)) return false;

        $mails = explode(",", $mail);
        for ($i = 0; $i < count($mails); $i++) {
            $mails[$i] = trim($mails[$i]);
        }
        return implode(", ", $mails);
    }

}