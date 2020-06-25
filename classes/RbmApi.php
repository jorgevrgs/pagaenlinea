<?php
/**
* 2018 Jorge Vargas.
*
* NOTICE OF LICENSE
*
* This source file is subject to the End User License Agreement (EULA)
*
* See attachmente file LICENSE
*
* @author    Jorge Vargas <https://addons.prestashop.com/contact-form.php?id_product=31085>
* @copyright 2007-2018 Jorge Vargas
*
* @see      http://addons.prestashop.com/es/2_community?contributor=3167
*
* @license   End User License Agreement (EULA)
*
* @version   2.0
*/
if (!defined('_PS_VERSION_')) {
    exit;
}

class Classes_RbmApi
{
    /**
     * @var string
     */
    private $idUsuario;

    /**
     * @var string
     */
    private $clave;

    /**
     * @var int
     */
    private $idAdquiriente;

    /**
     * @var int
     */
    private $idTransaccion;

    /**
     * @var int
     */
    private $idTransaccionTerminal = 0;

    /**
     * @var string
     */
    private $idTerminal;

    /**
     * @var string
     */
    private $tipoTerminal;

    /**
     * @var array
     */
    private $errors = array();

    /**
     * @var false|array
     */
    private $lastResponse = false;

    // Buttons
    const RBM_TEST_BUTTON = 'https://www.pagosrbm.com';
    const RBM_PROD_BUTTON = 'https://www.pagaenlinearbm.com';

    // Call functions
    const RBM_GET_TRANSACTION_ID = 'IniciarTransaccionDeCompra';
    const RBM_GET_TRANSACTION_STATUS = 'ConsultarEstadoDePago';

    public function __construct()
    {
        // Check if SOAP is enabled.
        if (false === extension_loaded('soap')) {
            throw new PrestaShopException('SOAP is not installed on your server');
        }

        $idUsuario = Configuration::get('PAGAENLINEA_ID_USUARIO');
        $clave = Configuration::get('PAGAENLINEA_CLAVE');

        if (empty($idUsuario)
        || empty($clave)) {
            return false;
        }
        $this->is_test = !Configuration::get('PAGAENLINEA_LIVE_MODE');
        $this->allowed_ips = explode(',', Configuration::get('PAGAENLINEA_TEST_IP'));

        $this->setIdUsuario($idUsuario);
        $this->setClave($clave);
        $this->setIdAdquiriente(Configuration::get('PAGAENLINEA_ID_ADQUIRENTE'));
        $this->setIdTerminal(Configuration::get('PAGAENLINEA_ID_TERMINAL_1'));
        $this->setTipoTerminal('GlobalPay');

        return true;
    }

    /**
     * @param int    $idTransaccion
     * @param string $idTerminal
     *
     * @return false | string $buttonUrl
     */
    public function getButtonUrl($idTransaccion = null, $idTerminal = null)
    {
        if (is_null($idTransaccion)) {
            $idTransaccion = $this->getIdTransaccion();
        }

        if (is_null($idTerminal)) {
            $idTerminal = $this->getIdTerminal();
        }

        if (!$this->getIdTerminal()
        || !$this->getIdTransaccion()) {
            return false;
        }

        $buttonUrl = self::RBM_PROD_BUTTON;
        if ($this->is_test) {
            $buttonUrl = self::RBM_TEST_BUTTON;
        }

        $buttonUrl .= '/GlobalPayWeb/gp/realizarPago.xhtml?idTerminal={ID_TERMINAL}&idTransaccion={ID_TRANSACTION}';

        $buttonUrl = str_replace('{ID_TERMINAL}', $idTerminal, $buttonUrl);
        $buttonUrl = str_replace('{ID_TRANSACTION}', $idTransaccion, $buttonUrl);

        return $buttonUrl;
    }

    public function getIdUsuario()
    {
        return $this->idUsuario;
    }

    public function setIdUsuario($idUsuario)
    {
        $this->idUsuario = $idUsuario;

        return true;
    }

    public function getClave()
    {
        return $this->clave;
    }

    public function setClave($clave)
    {
        $this->clave = $clave;

        return true;
    }

    /**
     * @param array $this->errors
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param string $message
     */
    public function setError($message)
    {
        if (is_string($message)
        && $message) {
            $this->errors[] = '[REDEBAN ERROR]: '.pSQL($message);
        }
    }

    /**
     * @param array $response
     */
    public function setResponseError($response)
    {
        if (isset($response['infoRespuesta']['codRespuesta'])
        && isset($response['infoRespuesta']['descRespuesta'])
        && isset($response['infoRespuesta']['estado'])) {
            $this->setError(
                Context::getContext()->getTranslator()->trans(
                    'An error code "%code%" has occured with description "%descRespuesta%" and status "%estado%".',
                    array(
                        '%codRespuesta%' => $response['infoRespuesta']['codRespuesta'],
                        '%descRespuesta%' => $response['infoRespuesta']['descRespuesta'],
                        '%estado%' => $response['infoRespuesta']['estado'],
                    ),
                    'PagaEnLinea-RbmApi'
                )
            );
        }

        return false;
    }

    /**
     * @param SoapClient $client
     */
    public static function AddLog(SoapClient $client)
    {
        Logger::AddLog('__getLastRequest: '.$client->__getLastRequest());
        Logger::AddLog('__getLastRequestHeaders: '.$client->__getLastRequestHeaders());
        Logger::AddLog('__getLastResponse: '.$client->__getLastResponse());
        Logger::AddLog('__getLastResponseHeaders: '.$client->__getLastResponseHeaders());
    }

    /**
     * @return string $idTransaccionTerminal
     */
    public function getidTransaccionTerminal()
    {
        return $this->idTransaccionTerminal;
    }

    /**
     * @param int $id number between 0 and 999999
     */
    public function setIdTransaccionTerminal($id)
    {
        $this->idTransaccionTerminal = (int) abs(Tools::substr($id, -6));

        return true;
    }

    public function getIdAdquiriente()
    {
        return $this->idAdquiriente;
    }

    public function setIdAdquiriente($idAdquiriente)
    {
        $this->idAdquiriente = $idAdquiriente;

        return true;
    }

    public function getIdTerminal()
    {
        return $this->idTerminal;
    }

    public function setIdTerminal($idTerminal)
    {
        $this->idTerminal = $idTerminal;

        return true;
    }

    public function getIdTransaccion()
    {
        return $this->idTransaccion;
    }

    public function setIdTransaccion($idTransaccion)
    {
        $this->idTransaccion = $idTransaccion;

        return true;
    }

    public function getTipoTerminal()
    {
        return $this->tipoTerminal;
    }

    public function setTipoTerminal($tipoTerminal)
    {
        $this->tipoTerminal = $tipoTerminal;

        return true;
    }

    public function setLastResponse($response)
    {
        if (isset($response['infoPago']['fechaTransaccion'])) {
            $response['infoPago']['fechaTransaccion'] = date(
                'Y-m-d H:i:s',
                strtotime($response['infoPago']['fechaTransaccion'])
            );
        }

        if (isset($response['infoPago']['montoTotal'])) {
            $response['infoPago']['montoTotal'] = (float) $response['infoPago']['montoTotal'];
        }

        if (isset($response['infoPago']['costoTransaccion'])) {
            $response['infoPago']['costoTransaccion'] = Tools::displayPrice(
                $response['infoPago']['costoTransaccion']
            );
        }

        if (isset($response['infoRespuesta']['estado'])) {
            $response['infoRespuesta']['estado'] = trim($response['infoRespuesta']['estado']);
        }

        $this->lastResponse = $response;

        return true;
    }

    public function getLastResponse()
    {
        return $this->lastResponse;
    }

    /**
     * Get transaction ID to allow button called.
     *
     * @return array $response {
     *
     *   @var array $cabeceraRespuesta {
     *     @var array $infoPuntoInteraccion {
     *       @var string $tipoTerminal = "GlobalPay"
     *       @var string $idTerminal" = "ESB12345"
     *       @var string $idAdquiriente = "0012345679"
     *       @var int idTransaccionTerminal = 0-999999
     *     }
     *   }
     * With error
     *  @var object $infoRespuesta {
     *    @var string $codRespuesta = "9008"
     *    @var string $descRespuesta = "Identificador de la transaccion
     *    (idTerminalTransaccion) esta duplicado para la fecha "
     *    @var string $estado" = "Rechazado"
     *   }
     * Successfull
     *  @var array $infoRespuesta {
     *    @var string $codRespuesta = "00"
     *    @var string $descRespuesta = "Operacion Exitosa"
     *    @var string $estado" = "Recibida"
     *   }
     *  @var array $infoTransaccionResp {
     *    @var string $idTransaccionActual = "12345678901234"
     *   }
     * }
     */
    public function iniciarTransaccionDeCompra(Cart $cart)
    {
        $customer = new Customer($cart->id_customer);
        if (!Validate::isLoadedObject($cart)
        || !Validate::isLoadedObject($customer)) {
            return false;
        }

        $this->setIdTransaccionTerminal((int) $cart->id);
        $cart_summary = $cart->getSummaryDetails();

        $params = array(
            'credenciales' => array(
                'idUsuario' => $this->getIdUsuario(),
                'clave' => $this->getClave(),
            ),
            'cabeceraSolicitud' => array(
                'infoPuntoInteraccion' => array(
                    'tipoTerminal' => $this->getTipoTerminal(),
                    'idTerminal' => $this->getIdTerminal(),
                    'idAdquiriente' => $this->getIdAdquiriente(),
                    'idTransaccionTerminal' => (int) $this->getIdTransaccionTerminal(),
                ),
            ),
            'infoPersona' => array(
                'nombres' => $customer->firstname,
                'apellidos' => $customer->lastname,
                'correo' => $customer->email,
            ),
            'infoCompra' => array(
                'numeroFactura' => $cart->id,
                'montoTotal' => $cart_summary['total_price'],
                'infoImpuestos' => array(
                    array(
                        'tipoImpuesto' => 'IVA',
                        'monto' => $cart_summary['total_tax'],
                    ),
                ),
                'montoDetallado' => array(
                    array(
                        'tipoMontoDetallado' => 'BaseDevolucionIVA',
                        'monto' => $cart_summary['total_price_without_tax'],
                    ),
                ),
            ),
        );
        if (!$cart_summary['total_tax']) {
            unset($params['infoCompra']['infoImpuestos']);
        }

        $address_invoice = Address::getCountryAndState($cart->id_address_invoice);
        $address_delivery = Address::getCountryAndState($cart->id_address_delivery);

        if (!empty($customer->siret)) {
            $params['infoPersona']['idPersona'] = array(
                'tipoDocumento' => 'CC',
                'numDocumento' => (int) $customer->siret,
            );
        } elseif (!empty($address_invoice['dni'])) {
            $params['infoPersona']['idPersona'] = array(
                'tipoDocumento' => 'CC',
                'numDocumento' => (int) $address_invoice['dni'],
            );
        } elseif (!empty($address_invoice['vat_number'])) {
            $params['infoPersona']['idPersona'] = array(
                'tipoDocumento' => 'CC',
                'numDocumento' => (int) $address_invoice['vat_number'],
            );
        } elseif (!empty($address_delivery['dni'])) {
            $params['infoPersona']['idPersona'] = array(
                'tipoDocumento' => 'CC',
                'numDocumento' => (int) $address_delivery['dni'],
            );
        } elseif (!empty($address_delivery['vat_number'])) {
            $params['infoPersona']['idPersona'] = array(
                'tipoDocumento' => 'CC',
                'numDocumento' => (int) $address_delivery['vat_number'],
            );
        }

        $response = $this->apicall(self::RBM_GET_TRANSACTION_ID, $params);

        if (is_array($response)
        && !empty($response['infoTransaccionResp']['idTransaccionActual'])) {
            $this->setIdTransaccion($response['infoTransaccionResp']['idTransaccionActual']);

            return (int) $this->getIdTransaccion();
        }

        return $this->setResponseError($response);
    }

    /**
     * @param null|int $id_transaction
     *
     * @return false|array $response array {
     *
     *   @var array $cabeceraRespuesta {
     *     @var array $infoPuntoInteraccion {
     *       @var string $tipoTerminal = "GlobalPay"
     *       @var string $idTerminal" = "ESB12345"
     *       @var string $idAdquiriente = "0012345679"
     *       @var int idTransaccionTerminal = 0-999999
     *     }
     *   }
     *   infoPago => Array (7)
     *     franquicia => "MASTERCARD"
     *     tipoMedioDePago => "Credito"
     *     fechaTransaccion => "2017-03-14T18:35:45.000-05:00"
     *     numeroAprobacion => "342372"
     *     montoTotal => "397460"
     *     costoTransaccion => "0"
     *     idTransaccionAutorizador => 19659
     * Successfull
     *  @var array $infoRespuesta {
     *    @var string $codRespuesta = "00"
     *    @var string $descRespuesta = "Transaccion iniciada" / "Transaccion aprobada"
     *    @var string $estado" = "Iniciada" / "Aprobada"
     *   }
     * }
     */
    public function consultarEstadoDePago($id_transaction = null)
    {
        $params = array(
            'credenciales' => array(
                'idUsuario' => $this->getIdUsuario(),
                'clave' => $this->getClave(),
            ),
            'cabeceraSolicitud' => array(
                'infoPuntoInteraccion' => array(
                    'tipoTerminal' => $this->getTipoTerminal(),
                    'idTerminal' => $this->getIdTerminal(),
                    'idAdquiriente' => $this->getIdAdquiriente(),
                    'idTransaccionTerminal' => (int) $this->getIdTransaccionTerminal(),
                ),
            ),
            'idTransaccion' => !empty($id_transaction) ? $id_transaction : $this->getIdTransaccion(),
        );

        $response = $this->apiCall(self::RBM_GET_TRANSACTION_STATUS, $params);

        if (isset($response['infoRespuesta']['estado'])) {
            return trim($response['infoRespuesta']['estado']);
        }

        return $this->setResponseError($response);
    }

    /**
     * Handles the API call for basic functions.
     *
     * @param string $function_name
     * @param array  $params
     *
     * @return false|array $response
     */
    private function apiCall($function_name, $params = null)
    {
        if ($this->is_test
        && !in_array(Tools::getRemoteAddr(), $this->allowed_ips)) {
            $this->setError(
                Context::getContext()->getTranslator()->trans(
                    'Your IP Address %remoteAddr% is not allowed in test mode',
                    array(
                        '%remoteAddr%' => Tools::getRemoteAddr(),
                    ),
                    'PagaEnLinea-RbmApi'
                )
            );

            return false;
        }

        if ($this->is_test) {
            Logger::AddLog('Calling transaction redeban...');
        }

        $options = array(
            'trace'              => true,
            'exceptions'         => true,
            'connection_timeout' => 10,
            'user_agent'         => 'Prestahop_'._PS_VERSION_,
            'encoding'           => 'UTF-8',
            'login'              => $this->getIdUsuario(),
            'password'           => $this->getClave(),
        );

        $environment = $this->is_test ? 'test' : 'prod';
        $certFile = $this->is_test ? 'www.txstestrbm.com.cer' : 'www.txsprodrbm.com.cer';

        $module = Module::getInstanceByName('pagaenlinea');
        if (!Validate::isLoadedObject($module)) {
            throw new PrestaShopException('An error ocurred when trying to access module PagaEnLinea');
        }

        $wsdlsPath = $module->getLocalPath().'libraries/wsdl/'.$environment.'/';
        $certsPath = $module->getPathUri().'libraries/cert/'.$environment.'/';

        $options['local_cert'] = $certsPath.$certFile;
        $wsdl = $wsdlsPath.'GlobalPayServicioDePago.wsdl';

        try {
            $client = new SoapClient($wsdl, $options);
            $response = $client->$function_name($params);

            $response = json_decode(json_encode($response), true);
            $this->setLastResponse($response);

            return $response;
        } catch (Exception $e) {
            $this->setError(
                Context::getContext()->getTranslator()->trans(
                    'An error code %code% has occurred with message %message%',
                    array(
                        '%code%' => pSQL($e->getCode()),
                        '%message%' => pSQL($e->getMessage()),
                    ),
                    'PagaEnLinea-RbmApi'
                )
            );
            Logger::AddLog($e->getMessage());
            self::AddLog($client);

            return false;
        }
    }
}
