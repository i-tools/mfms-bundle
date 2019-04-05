<?php

declare(strict_types=1);

/**
 * @file        MFMSGateway.php
 * @description Сервис для отправки sms-сообщений через шлюз https://mfms.ru/
 *
 * PHP Version  PHP 7.2.15
 *
 * @created     14.03.2019
 */

namespace itools\MFMSGatewayBundle;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Psr\Log\LoggerInterface;
use GuzzleHttp\Psr7\Response as CurlResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class MFMSGateway
 */
class MFMSGateway
{
    /**
     * @var string
     */
    private $url;
    /**
     * @var string
     */
    private $login;
    /**
     * @var string
     */
    private $password;
    /**
     * @var array
     */
    private $messages;
    /**
     * @var
     */
    private $curl;
    /**
     * @var LoggerInterface
     */
    private $log;

    /**
     * MFMSGateway constructor.
     *
     * @param string $url
     * @param string $login
     * @param string $password
     * @param LoggerInterface $logger
     */
    public function __construct(string $url = null, string $login = null, string $password = null, LoggerInterface $logger = null)
    {
        $this->url = $url;
        $this->login = $login;
        $this->password = $password;
        $this->messages = [];
        $this->log = $logger;
        $this->curl = new Client(['base_uri' => $this->url]);

        new \Swift_SmtpTransport();
    }

    /**
     * Очистка списка сообщений
     */
    protected function flushMessages()
    {
        unset($this->messages);
    }

    /**
     * Добавление сообщения в список
     *
     * @param string $recipient
     * @param string $subject
     * @param string $msg
     */
    public function addMessage(string $recipient, string $subject, string $msg)
    {
        $this->messages[] = [
            'address' => $recipient,
            'subject' => $subject,
            'text' => $msg,
            'uid' => uniqid()
        ];
    }

    /**
     * Отправка сообщения клиентам
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function sendMessages(): array
    {
        $countApproach = 0;
        do {
            try {
                $response = $this->curl->request('GET', 'send', [
                    'query' => [
                        'login' => $this->login,
                        'pass' => $this->password,
                        'address' => $this->messages[0]['address'],
                        'subject' => $this->messages[0]['subject'],
                        'text' => $this->messages[0]['text'],
                        'clientId' => $this->messages[0]['uid']
                    ]
                ]);
            } catch (ClientException $e) {
                $this->log->error($e->getCode() . ': '.$e->getMessage());
            }

            //var_dump($response->getBody()->getContents());
            $result = $this->parseMessageStatus($response->getBody()->getContents());

            if ( $result['code'] == JsonResponse::HTTP_OK) {
                break;
            } else {
                $this->log->error('MFMSGateway: Error send SMS message "'.$result['msg'].'" ');
            }

            $countApproach++;
        } while ($countApproach < 3);

        $this->flushMessages();

        return $result;
    }

    /**
     * @param $msgId
     * @return array|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getMessageStatus($msgId): ?array
    {
        try {
            $response = $this->curl->request('GET', 'status', [
                'query' => [
                    'login' => $this->login,
                    'pass' => $this->password,
                    'providerId' => $msgId
                ]
            ]);
        } catch (ClientException $e) {
            $this->log->error('MFMS ' . $e->getCode() . ': '.$e->getMessage());
        }
        return $this->parseMessageStatus($response->getBody()->getContents());
    }

    /**
     * @param string $status
     * @return array
     */
    private function parseMessageStatus(string $status): array
    {

        $statusMsg = preg_split('/;/', $status, -1, PREG_SPLIT_NO_EMPTY);

        switch ($statusMsg[0]) {
            case 'ok': {
                $result['code'] = 200;
                $result['msg'] = 'Запрос успешно обработан';
                break;
            }
            case 'error-auth': {
                $result['code'] = 401;
                $result['msg'] = 'Указаны неверные логин\пароль';
            }
            case 'error-address-format': {
                $result['code'] = 422;
                $result['msg'] = 'Ошибка формата адреса';
                break;
            }
            case 'error-syntax': {
                $result['code'] = 422;
                $result['msg'] = 'Ошибка в синтаксисе запроса или переданы не все обязательные параметры';
                break;
            }
            case 'error-address-unknown': {
                $result['code'] = 403;
                $result['msg'] = 'Отправка на указанный адрес запрещена';
                break;
            }
            case 'error-subject-format': {
                $result['code'] = 422;
                $result['msg'] = 'Ошибка формата отправителя';
                break;
            }
            case 'error-subject-unknown': {
                $result['code'] = 403;
                $result['msg'] = 'Отправитель запрещен на платформе';
                break;
            }
            case 'error-system': {
                $result['code'] = 500;
                $result['msg'] = 'При обработке сообщения произошла системная ошибка';
                break;
            }
            default : {
                $result['code'] = 500;
                $result['msg'] = 'Неизвестная ошибка';
                break;
            }
        }

        return $result;
    }
}