<?php
    
    namespace PeterBourneComms\CMS;
    
    use PDO;
    use PDOException;
    use Exception;
    
    use PHPMailer\PHPMailer\PHPMailer;
    
    
    /**
     * Class to send a single email - EXTENDING PHPMailer now as my version was rubbish!!
     *
     * @author Peter Bourne
     * @version 3.2
     *
     * @history
     *
     * ---          1.0     Original
     * 06.05.2020   1.1     Added plain text functionality and fixed the Reply-To white-space issue
     * 11.04.2021   1.2     Added Reply To address functionality
     * 29.04.2022   2.0     Re-wrote a little bit - and added iCal sending ability
     * 20/05/2022   3.0     Re-wrote again - using PHPMailer as the main engine
     * 01/08/2022   3.1     Changed ical attachment format to improve success
     * 23/04/2023   3.2     Added general attachment capability - mainly PDF for EACR QBO functionality. uses actual file in filepath - also changed to UTF-8 character set
     *
     */
    class PBEmail
    {
        protected $_recipient;
        protected $_sender_email;
        protected $_sender_name;
        protected $_subject;
        protected $_html_message;
        protected $_text_message;
        protected $_template_file;
        protected $_headers;
        protected $_replyto_email;
        protected $_replyto_name;
        protected $_ical_content;
        protected $_file_attachment;
        
        public function __construct()
        {
            return true;
        }
        
        
        public function sendMail()
        {
            //Check we have all properties set that we absolutely need
            if (!filter_var($this->_recipient, FILTER_VALIDATE_EMAIL)) {
                error_log('CMS\PBEmail->sendMail(): You need to specify a valid email address for the Recipient');
                return false;
            }
            if (!filter_var($this->_sender_email, FILTER_VALIDATE_EMAIL)) {
                error_log('CMS\PBEmail->sendMail(): You need to specify a valid email address for the Sender');
                return false;
            }
            if ($this->_subject == '') {
                error_log('CMS\PBEmail->sendMail(): You need to specify a subject for the email');
                return false;
            }
            if ($this->_html_message == '') {
                error_log('CMS\PBEmail->sendMail(): You need to specify a message');
                return false;
            }
            if ($this->_template_file == '' || !file_exists($this->_template_file)) {
                error_log('CMS\PBEmail->sendMail(): The template file you specified does not exist');
                return false;
            }
            
            //Default the replyto as sender
            if ($this->_replyto_email == '' || !filter_var($this->_replyto_email, FILTER_VALIDATE_EMAIL)) {
                $this->_replyto_email = $this->_sender_email;
            }
            
            //If we only have one of html or text - default the other
            if ($this->_html_message == '' && $this->_text_message != '') {
                $this->_html_message = "<p>".nl2br($this->_text_message)."</p>";
            } elseif ($this->_html_message != '' && $this->_text_message == '') {
                $this->_text_message = strip_tags($this->_html_message);
            }
            
            
            //Prepare the email
            
            //Sender
            /*$sender = "";
            if ($this->_sender_name != '') {
                $sender .= $this->_sender_name." <";
            }
            $sender .= $this->_sender_email;
            if ($this->_sender_name != '') {
                $sender .= ">";
            }
            
            //Reply to
            $replyto = "";
            if ($this->_replyto_name != '') {
                $replyto .= $this->_replyto_name." <";
            }
            $replyto .= $this->_replyto_email;
            if ($this->_replyto_name != '') {
                $replyto .= ">";
            }
            */
            
            //Open template for a nicely formatted email
            $fp = fopen($this->_template_file, "r");
            $content = fread($fp, filesize($this->_template_file));
            fclose($fp);
            //finished read - now replace our placeholder with the message body
            
            
            //HTML message part
            $htmlcontent = str_replace("[message]", $this->_html_message, $content);
            
            //Plain text part
            $textcontent = $this->_text_message;
            
            //Create a new PHPMailer instance
            $mail = new PHPMailer();
            $mail->CharSet = 'utf-8';
            //Set PHPMailer to use the sendmail transport
            $mail->isSendmail();
            //Set who the message is to be sent from
            $mail->setFrom($this->_sender_email, $this->_sender_name);
            //Set an alternative reply-to address
            $mail->addReplyTo($this->_replyto_email, $this->_replyto_name);
            //Set who the message is to be sent to
            $mail->addAddress($this->_recipient);
            //Set the subject line
            $mail->Subject = $this->_subject;
            //Set HTML
            $mail->Body = $htmlcontent;
            //Set plain text
            $mail->AltBody = $textcontent;
            //Attach an image file
            //$mail->addAttachment('images/phpmailer_mini.png');
            //Attach ical
            if ($this->_ical_content != '') {
                //$mail->Ical = $this->_ical_content;
                //August 2022 change to calendar attachment format
                $mail->addStringAttachment($this->_ical_content,'ical.ics','base64','text/calendar');
            }
            
            //Attach file attachment
            if ($this->_file_attachment != '') {
                $mail->addAttachment($this->_file_attachment);
            }

            $result = $mail->send();
            
            return $result;
            
            /*
            //Tech set up
            $boundary1 = uniqid('np');
            $boundary2 = uniqid('np2');
            $boundary3 = uniqid('np3');
            $eol = PHP_EOL;
            
            $headertype = "Content-Type: multipart/mixed; boundary=\"" . $boundary1."\"".$eol;
            $headertype .= "Content-Type: multipart/related; boundary=\"".$boundary2."\" charset=\"utf-8\"".$eol;
            $headertype .= "Content-Type: multipart/alternative; boundary=\"".$boundary3."\" charset=\"utf-8\"".$eol;
            
            $message = "This is multipart message using MIME.".$eol;
            $message .= "--".$boundary1.$eol;
            //$message .= "Content-Type: multipart/related; boundary=\"".$boundary2."\" charset=\"utf-8\"".$eol.$eol;
            $message .= "--".$boundary2.$eol;
            //$message .= "Content-Type: multipart/alternative; boundary=\"".$boundary3."\" charset=\"utf-8\"".$eol.$eol;
            //Plain text
            $message .= "--".$boundary3.$eol;
            $message .= "Content-Type: text/plain; charset=\"utf-8\"".$eol;
            $message .= "Content-Transfer-Encoding: 7bit".$eol.$eol;
            $message .= $textcontent.$eol.$eol;
    
            //HTML
            $message .= "--".$boundary3.$eol;
            $message .= "Content-Type: text/html; charset=\"utf-8\"".$eol;
            $message .= "Content-Transfer-Encoding: 7bit".$eol.$eol;
            $message .= $htmlcontent.$eol.$eol;
            $message .= "--".$boundary3."--".$eol;
            
            //Calendar?
            if ($this->_ical_content != '') {
                $message .= $eol;
                $message .= "--".$boundary2.$eol;
                $message .= 'Content-Type: text/calendar;name="meeting.ics";method=REQUEST'.$eol;
                $message .= "Content-Transfer-Encoding: 8bit.$eol.$eol;
                $message .= $this->_ical_content;
            }
    
            $message .= "--".$boundary2."--".$eol;
            
            //End message
            $message .= "--".$boundary1."--".$eol;
            //$message .= "--".$eol;
            
            //Now prepare any additional headers - including the sender
            $headers = "";
            $headers .= "Return-Path: ".$sender.$eol;
            $headers .= "Reply-To: ".$replyto.$eol;
            $headers .= "Sender: ".$sender.$eol;
            $headers .= "From: ".$sender.$eol;
            $headers .= "MIME-Version: 1.0".$eol;
            $headers .= $headertype.$eol;

            $headers .= $this->_headers;

            //Send the email
            $result = mail($this->_recipient,$this->_subject,$message,$headers);
            
            return $result;
            
            */
        }
        
        
        public function getRecipient()
        {
            return $this->_recipient;
        }
        
        public function setRecipient($recipient)
        {
            $this->_recipient = $recipient;
        }
        
        public function getSenderEmail()
        {
            return $this->_sender_email;
        }
        
        public function setSenderEmail($sender_email)
        {
            $this->_sender_email = $sender_email;
        }
        
        public function getSenderName()
        {
            return $this->_sender_name;
        }
        
        public function setSenderName($sender_name)
        {
            $this->_sender_name = $sender_name;
        }
        
        public function getSubject()
        {
            return $this->_subject;
        }
        
        public function setSubject($subject)
        {
            $this->_subject = $subject;
        }
        
        public function getHtmlMessage()
        {
            return $this->_html_message;
        }
        
        public function setHtmlMessage($message)
        {
            $this->_html_message = FixOutput($message);
        }
        
        public function getTemplateFile()
        {
            return $this->_template_file;
        }
        
        public function setTemplateFile($template_file)
        {
            $this->_template_file = $template_file;
        }
        
        public function getHeaders()
        {
            return $this->_headers;
        }
        
        public function setHeaders($headers)
        {
            $this->_headers = $headers;
        }
        
        /**
         * @return mixed
         */
        public function getTextMessage()
        {
            return $this->_text_message;
        }
        
        /**
         * @param mixed $text_message
         */
        public function setTextMessage($text_message)
        {
            $this->_text_message = FixOutput($text_message);
        }
        
        /**
         * @return mixed
         */
        public function getReplytoEmail()
        {
            return $this->_replyto_email;
        }
        
        /**
         * @param mixed $replyto_email
         */
        public function setReplytoEmail($replyto_email): void
        {
            $this->_replyto_email = $replyto_email;
        }
        
        /**
         * @return mixed
         */
        public function getReplytoName()
        {
            return $this->_replyto_name;
        }
        
        /**
         * @param mixed $replyto_name
         */
        public function setReplytoName($replyto_name): void
        {
            $this->_replyto_name = $replyto_name;
        }
        
        /**
         * @return mixed
         */
        public function getIcalContent()
        {
            return $this->_ical_content;
        }
        
        /**
         * @param mixed $ical_content
         */
        public function setIcalContent($ical_content): void
        {
            $this->_ical_content = $ical_content;
        }
        
        /**
         * @return mixed
         */
        public function getFileAttachment()
        {
            return $this->_file_attachment;
        }
        
        /**
         * @param mixed $file_attachment
         */
        public function setFileAttachment($file_attachment): void
        {
            $this->_file_attachment = $file_attachment;
        }
        
        
        
    }