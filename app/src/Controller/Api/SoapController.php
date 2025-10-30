<?php

namespace App\Controller\Api;

use App\Application\CreateOrderFromSoapUseCase;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SoapController extends AbstractController
{
    public function __construct(private readonly CreateOrderFromSoapUseCase $createOrder)
    {
    }

    #[Route('/api/soap/wsdl', name: 'api_soap_wsdl', methods: ['GET'])]
    #[OA\Get(
        path: '/api/soap/wsdl',
        summary: 'Get SOAP WSDL definition',
        responses: [
            new OA\Response(
                response: 200,
                description: 'WSDL XML',
                content: new OA\MediaType(mediaType: 'text/xml')
            ),
        ]
    )]
    public function wsdl(Request $request): Response
    {
        $baseUrl = $request->getSchemeAndHttpHost();
        $wsdl = sprintf('<?xml version="1.0" encoding="UTF-8"?>
<definitions xmlns="http://schemas.xmlsoap.org/wsdl/"
             xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/"
             xmlns:tns="http://tile.expert/api/soap"
             targetNamespace="http://tile.expert/api/soap">
    <types>
        <schema xmlns="http://www.w3.org/2001/XMLSchema" targetNamespace="http://tile.expert/api/soap">
            <element name="CreateOrderRequest">
                <complexType>
                    <sequence>
                        <element name="name" type="string"/>
                        <element name="email" type="string" minOccurs="0"/>
                        <element name="status" type="int" minOccurs="0"/>
                    </sequence>
                </complexType>
            </element>
            <element name="CreateOrderResponse">
                <complexType>
                    <sequence>
                        <element name="id" type="int"/>
                        <element name="hash" type="string"/>
                    </sequence>
                </complexType>
            </element>
        </schema>
    </types>
    <message name="CreateOrderRequest">
        <part name="parameters" element="tns:CreateOrderRequest"/>
    </message>
    <message name="CreateOrderResponse">
        <part name="parameters" element="tns:CreateOrderResponse"/>
    </message>
    <portType name="OrderServicePortType">
        <operation name="CreateOrder">
            <input message="tns:CreateOrderRequest"/>
            <output message="tns:CreateOrderResponse"/>
        </operation>
    </portType>
    <binding name="OrderServiceBinding" type="tns:OrderServicePortType">
        <soap:binding style="document" transport="http://schemas.xmlsoap.org/soap/http"/>
        <operation name="CreateOrder">
            <soap:operation soapAction="http://tile.expert/api/soap/CreateOrder"/>
            <input>
                <soap:body use="literal"/>
            </input>
            <output>
                <soap:body use="literal"/>
            </output>
        </operation>
    </binding>
    <service name="OrderService">
        <port name="OrderServicePort" binding="tns:OrderServiceBinding">
            <soap:address location="%s/api/soap"/>
        </port>
    </service>
</definitions>', $baseUrl);

        return new Response($wsdl, 200, ['Content-Type' => 'text/xml; charset=utf-8']);
    }

    #[Route('/api/soap', name: 'api_soap_server', methods: ['POST'])]
    #[OA\Post(
        path: '/api/soap',
        summary: 'SOAP server endpoint',
        requestBody: new OA\RequestBody(
            content: new OA\MediaType(mediaType: 'text/xml')
        ),
        responses: [
            new OA\Response(response: 200, description: 'SOAP response'),
            new OA\Response(response: 500, description: 'SOAP extension not loaded'),
        ]
    )]
    public function server(Request $request): Response
    {
        if (!extension_loaded('soap')) {
            return new Response('SOAP extension not loaded', 500);
        }

        // Use non-WSDL mode with explicit location
        $options = [
            'uri' => 'http://tile.expert/api/soap',
            'location' => $request->getSchemeAndHttpHost() . '/api/soap',
            'soap_version' => SOAP_1_2,
        ];
        
        $server = new \SoapServer(null, $options);
        $server->setObject($this);
        $server->handle();
        
        return new Response();
    }

    /**
     * @param string $name
     * @param string|null $email
     * @param int $status
     * @return array{id: int, hash: string}
     */
    public function CreateOrder(string $name, ?string $email = null, int $status = 1): array
    {
        $xml = sprintf(
            '<Envelope><Body><create><name>%s</name><email>%s</email><status>%d</status></create></Body></Envelope>',
            htmlspecialchars($name, ENT_XML1),
            htmlspecialchars($email ?? '', ENT_XML1),
            $status
        );
        return $this->createOrder->execute($xml);
    }
}