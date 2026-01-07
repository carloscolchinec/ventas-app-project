<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>CONTRATO DE ADHESIÓN</title>
    <style>
        body {
            font-family: "Times New Roman", Times, serif;

            font-size: 11px;
            line-height: 1.6;
            margin: 40px;
        }

        h2 {
            text-align: center;
            text-transform: uppercase;
        }

        .titulo {
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 20px;
        }

        .resaltado {
            background-color: yellow;
            font-weight: bold;
        }

        .resaltado-verde {
            background-color: #a3f5a3;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 6px;
        }

        ul {
            margin-top: 0;
        }

        .abonado {
            line-height: 0.9;
            margin-top: 10px;
            margin-bottom: 10px;
        }
    </style>

    <style>
        @page {
            margin: 100px 40px 60px 40px;
        }

        header {
            position: fixed;
            top: -40px;
            left: 0;
            right: 0;
            height: 60px;
        }

        body::before {
            content: "";
            position: fixed;
            top: 0%;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('{{ public_path('images/logo_socnetd.png') }}') no-repeat center center;
            background-size: 500px;
            opacity: 0.05;
            z-index: -1;
        }
    </style>


</head>

<body>

    <header>
        <table width="100%" style="border-collapse: collapse; table-layout: fixed;">
            <tr>
                <td style="width: 50%; text-align: left; padding-left: 40px; border: none;">
                    <img src="{{ public_path('images/logo_seroficom.png') }}" alt="Logo Seroficom"
                        style="height: 40px;">
                <td style="width: 50%; text-align: right; padding-right: 40px; border: none;">
                    <img src="{{ public_path('images/logo_socnet.png') }}" alt="Logo Socnet" style="height: 40px;">
                </td>
                </td>
            </tr>
        </table>
    </header>



    <h2>CONTRATO DE ADHESIÓN</h2>

    <p>Quienes comparecen libre y voluntariamente convienen en celebrar y suscribir el presente contrato de Prestación
        de Servicios, de conformidad con las cláusulas que a continuación se detallan:</p>

    <p class="titulo">CLAUSULA PRIMERA: LUGAR Y FECHA.- DATOS DE LOS COMPARECIENTES.-</p>
    <p>En la ciudad de<span style="font-weight: bold">
            {{ $datosPrestador['ciudad_fecha'] ?? 'GUAYAQUIL' }},
            {{ mb_strtoupper(\Carbon\Carbon::now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY'), 'UTF-8') }}
        </span> comparecen a celebrar, como en
        efecto celebran el presente Contrato de Adhesión, las siguientes personas: Uno.- El Sr. JORGE ARTURO SUAREZ
        INTRIAGO representante legal de la empresa SERVICIO DE OFICINA COMPUTARIZADO SEROFICOM S.A., a quien en adelante
        se lo denominará EL PRESTADOR, los datos se detallan a continuación:</p>

    <p class="titulo">DATOS DEL PRESTADOR:</p>
    <table>
        <tr>
            <td style="font-weight: bold">NOMBRE/RAZÓN SOCIAL</td>
            <td>SERVICIO DE OFICINA COMPUTARIZADO SEROFICOM S.A.</td>
        </tr>
        <tr>
            <td style="font-weight: bold">NOMBRE COMERCIAL</td>
            <td>SOCNET</td>
        </tr>
        <tr>
            <td style="font-weight: bold">DIRECCIÓN</td>
            <td>{{ $datosPrestador['direccion'] ?? 'BARRIO GUAYAQUIL #430' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold">PROVINCIA</td>
            <td>{{ $datosPrestador['provincia'] ?? 'SANTA ELENA' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold">CIUDAD</td>
            <td>{{ $datosPrestador['ciudad'] ?? 'SANTA ELENA' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold">CANTÓN</td>
            <td>{{ $datosPrestador['canton'] ?? 'SANTA ELENA' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold">PARROQUIA</td>
            <td>{{ $datosPrestador['parroquia'] ?? 'SAN JOSÉ DE ANCÓN' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold">N° DE TELÉFONO</td>
            <td>{{ $datosPrestador['telefono'] ?? '0958933197' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold">RUC</td>
            <td>0993238155001</td>
        </tr>
        <tr>
            <td style="font-weight: bold">CORREO ELECTRÓNICO</td>
            <td>info@seroficom.org</td>
        </tr>
        <tr>
            <td style="font-weight: bold">PÁGINA WEB</td>
            <td>www.seroficom.org</td>
        </tr>
    </table>

    <p class="titulo">DATOS DEL ABONADO/SUSCRIPTOR:</p>

    <div class="abonado">
        <p><strong>CEDULA:</strong> {{ $cliente->identificacion }}</p>
        <p><strong>NOMBRES:</strong> {{ $cliente->nombres }}</p>
        <p><strong>APELLIDOS:</strong> {{ $cliente->apellidos }}</p>
        <p><strong>NACIONALIDAD:</strong> ECUATORIANA</p>
        <p><strong>DIRECCIÓN COMPLETA:</strong> {{ $cliente->direccion }}</p>
        <p><strong>TELÉFONO:</strong>
            {{ empty($cliente->telefonos) ? 'No registrado' : (is_array($cliente->telefonos) ? implode(', ', $cliente->telefonos) : $cliente->telefonos) }}
        </p>
        <p><strong>EMAIL:</strong>
            {{ empty($cliente->correos) ? 'No registrado' : (is_array($cliente->correos) ? implode(', ', $cliente->correos) : $cliente->correos) }}
        </p>
        <p><strong>¿EL ABONADO ES ADULTO MAYOR O DISCAPACITADO?</strong>
            &nbsp;
            SI {!! $cliente->es_tercera_edad || $cliente->es_presenta_discapacidad ? 'X' : '&nbsp;' !!}
            &nbsp;&nbsp;&nbsp;&nbsp;
            NO {!! !$cliente->es_tercera_edad && !$cliente->es_presenta_discapacidad ? 'X' : '&nbsp;' !!}
        </p>
    </div>

    <p>
        En caso afirmativo, aplica el beneficio del 50% de reducción de la tarifa básica de internet de acuerdo al plan
        del prestador.
    </p>

    <div style="page-break-before: always;"></div> <!-- Separación solicitada por usuario -->
    <p class="titulo">CLÁUSULA SEGUNDA: OBJETO.-</p>
    <p>El prestador del servicio se compromete a proporcionar al abonado/suscriptor el/los siguiente(s) servicio(s) para
        lo cual el prestador dispone de los correspondientes títulos habilitantes otorgados por la ARCOTEL, de
        conformidad con el ordenamiento jurídico vigente:</p>
    <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
        <thead>
            <tr>
                <th style="border: 1px solid #ccc; padding: 6px;">SERVICIO</th>
                <th style="border: 1px solid #ccc; padding: 6px; text-align: center;">SELECCIONADO</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="border: 1px solid #ccc; padding: 6px;">Acceso a internet</td>
                <td style="border: 1px solid #ccc; padding: 6px; text-align: center;">X</td>
            </tr>
        </tbody>
    </table>

    <p>Las condiciones del/los servicio(s) que el abonado va a contratar se encuentran detalladas en el Anexo N° 1, el
        cual forma parte integrante del presente contrato.</p>

    <p class="titulo">CLÁUSULA TERCERA: VIGENCIA DEL CONTRATO.</p>
    <p>El presente contrato, tendrá un plazo de vigencia de <strong>12 MESES</strong>, y entrará en vigencia a partir de
        la fecha de instalación y prestación efectiva del servicio...</p>

    <p>El ABONADO acepta la renovación automática sucesiva del contrato: <strong>SI X &nbsp;&nbsp; NO</strong></p>

    <p class="titulo">CLÁUSULA CUARTA: PERMANENCIA MÍNIMA</p>
    <p>¿El ABONADO se acoge al período de permanencia mínima? <strong>SI X &nbsp;&nbsp; NO</strong></p>
    <ul>
        <li>Descuento del 100% en el servicio de instalación.</li>
    </ul>


    <p class="titulo">CLÁUSULA QUINTA: TARIFA Y FORMA DE PAGO</p>
    <p>Puede consultar las tarifas mensuales en el Anexo 1F. Si su forma de pago es débito bancario, encontrará el
        formulario de autorización en el Anexo 1.
    </p>

    <p><strong>Forma de pago:</strong>
        {{ mb_strtoupper($cliente->metodoPago->nombre ?? $cliente->metodo_pago_texto ?? 'NO DEFINIDO') }}
    </p>

    <p>La tarifa correspondiente al servicio contratado y efectivamente prestado, estará dentro de los techos tarifarios
        señalados por la ARCOTEL y en los títulos habilitantes correspondientes, en caso de que se establezcan, de
        conformidad con el ordenamiento jurídico vigente.</p>

    <p>En caso de que el ABONADO o suscritor desee cambiar su modalidad de pago a otra de las disponibles, deberá
        comunicar al PRESTADOR del servicio con quince (15) días de anticipación. El prestador del servicio, luego de
        haber sido comunicado, instrumentará la nueva forma de pago.</p>

    <p>Estos pagos se los realizará mensualmente dentro del 01 al 10 de cada mes, en el caso de que el ABONADO faltare a
        los pagos de dos meses consecutivos se procederá con la cancelación del servicio.</p>

    <p class="titulo">CLÁUSULA SEXTA: TERMINACIÓN DEL CONTRATO</p>
    <p>Los contratos podrán darse por terminado por cualquiera de las siguientes causas:</p>
    <p><strong>Por el Prestador del Servicio:</strong></p>
    <ol type="a">
        <li>Incumplimiento de las condiciones contractuales del abonado, o la consignación de datos erróneos o falsos.
        </li>
        <li>Si el abonado utiliza los servicios contratados para fines distintos a los convenidos o si los utiliza en
            prácticas contrarias a la ley.</li>
        <li>Por vencimiento del plazo de vigencia del contrato, cuando no exista renovación.</li>
        <li>Por falta de pago.</li>
        <li>Por las demás causas previstas en el Ordenamiento Jurídico Vigente.</li>
    </ol>

    <p><strong>Por el Abonado:</strong></p>
    <ol type="a">
        <li>Por decisión unilateral del abonado, suscriptor o cliente de dar por terminado el contrato.</li>
        <li>Por vencimiento del plazo de vigencia del contrato, cuando no exista renovación pactada.</li>
        <li>Por incumplimiento de las condiciones contractuales pactadas.</li>
        <li>Por las demás causas previstas en el Ordenamiento Jurídico Vigente.</li>
    </ol>


    <p>El no cancelar los saldos que estuvieron pendientes al momento de la presentación de la solicitud de terminación
        no podrá ser considerado como un impedimento para procesar y cancelar el contrato. Esto no significa que el
        prestador haya renunciado al cobro de dichos valores ya que los podrá cobrar en la forma y plazos establecidos
        en el ordenamiento jurídico a través de los medios legales correspondientes.</p>

    <p class="titulo">CLÁUSULA SÉPTIMA: COMPRA, ARRENDAMIENTO DE EQUIPOS</p>
    <p>Cuando sea procedente el arrendamiento o adquisición de equipos, por parte del abonado, toda la información
        pertinente será detallada en un Anexo adicional, suscrito por el abonado el cual contendrá los temas
        relacionados a las condiciones de los equipos adquiridos/arrendados, entre otras características se deberá
        incluir: cantidad, precio, marca, estado, y las condiciones de tal adquisición o arrendamiento, particularmente
        el tiempo en el que se pagará el arrendamiento o la compra del equipo, el valor mensual a cancelar o las
        condiciones de pago.</p>

    <p class="titulo">CLÁUSULA OCTAVA: USO DE INFORMACIÓN PERSONAL</p>
    <p>Los datos personales que los usuarios proporciones a los prestadores de servicios del régimen general de
        telecomunicaciones, no podrán ser usados para la promoción comercial de servicios o productos, inclusive de la
        propia operadora; salvo autorización y consentimiento expreso del ABONADO, el que constará como instrumento
        separado y distinto al presente contrato de prestación de servicios (contrato de adhesión) a través de medios
        físicos o electrónicos. En dicho instrumento se deberá dejar constancia expresa de los datos personales o
        información que están expresamente autorizados; el plazo de la autorización y el objetivo que esta utilización
        persigue, conforme lo dispuesto en el artículo 121 del Reglamento General a la Ley Orgánica de
        Telecomunicaciones.</p>

    <p class="titulo">CLÁUSULA NOVENA: RECLAMOS Y SOPORTE TÉCNICO</p>
    <p>El abonado podrá requerir soporte técnico o presentar reclamos al prestador de servicios a través de los
        siguientes medios o puntos:</p>
    <ul>
        <li>Medio electrónico: Página web: www.seroficom.org</li>
        <li>Oficinas de atención al usuario: Cantón Santa Elena/ Parroquia San José de Ancón/ Barrio Guayaquil #430</li>
        <li>Horarios de atención: 8:30 am a 6:00 pm</li>
        <li>Teléfonos: 0958933197 - 043903497</li>
        <li>Si su reclamo NO ha sido resuelto por el prestador, ingrese su queja a través del formulario respectivo en
            www.gob.ec.</li>
    </ul>

    <p class="titulo">CLÁUSULA DÉCIMA: NORMATIVA APLICABLE</p>
    <p>En la prestación del servicio, se entienden incluidos todos los derechos y obligaciones de los
        ABONADOS/SUSCRIPTORES, establecidos en la norma jurídica aplicable, así como también los derechos y obligaciones
        de los prestadores de servicios de telecomunicaciones y/o servicios de radiodifusión por suscripción, dispuesto
        en el marco regulatorio.</p>

    <p class="titulo">CLÁUSULA DÉCIMA PRIMERA: CONTROVERSIAS</p>
    <p>Las diferencias que surjan de la ejecución del presente Contrato, podrán ser resueltas por mutuo acuerdo entre
        las partes, sin perjuicio de que el abonado acuda con su reclamo, queja o denuncia, ante las autoridades
        administrativas que correspondan. De no llegarse a una solución, cualquiera de las partes podrá acudir ante los
        jueces competentes.</p>

    <p>No obstante lo indicado, las partes pueden pactar adicionalmente, someter sus controversias ante un centro de
        mediación o arbitraje, si así lo deciden expresamente, en cuyo caso el abonado deberá señalarlo en forma
        expresa</p>

    <p>El abonado, en caso de conflicto, acepta someterse a la mediación o arbitraje <strong>(puede significar costos en
            los que debe incurrir el abonado)</strong>. No aplica a empresas públicas prestadoras del servicio de
        telecomunicaciones:
        <strong>SI X NO </strong>
    </p>

    <img src="{{ public_path('storage/' . $cliente->firma) }}" alt="Firma del cliente"
        style="width:200px; height:auto;">
    <p>Firma de aceptación-sujeción a arbitraje:</p>


    <p class="titulo">CLÁUSULA DÉCIMA SEGUNDA: ANEXOS</p>
    <p>Son parte integrante del presente contrato:</p>
    <ul>
        <li>Anexo N° 1 “AUTORIZACION DEBITO BANCARIO”</li>
        <li>Anexo N° 1F “TÉCNICO – COMERCIAL” que contiene las “Condiciones particulares del Servicio”</li>
        <li>Anexo N° 2 Uso de información personal.</li>
    </ul>
    <p>Así como los demás anexos y documentos que se incorporen de conformidad con el ordenamiento jurídico.</p>

    <p class="titulo">CLÁUSULA DÉCIMA TERCERA: NOTIFICACIONES Y DOMICILIO</p>
    <p>Las notificaciones que correspondan, serán entregadas en el domicilio de cada una de las partes señalado en la
        cláusula primera del presente contrato. Cualquier cambio de domicilio debe ser comunicado por escrito a la otra
        parte en un plazo de 10 días, a partir del día siguiente en que el cambio se efectúe.</p>

    <p class="titulo">CLÁUSULA DÉCIMA CUARTA: EMPAQUETAMIENTO DE SERVICIOS</p>
    <p>La contratación incluye empaquetamiento de servicios: <strong>SI &nbsp;&nbsp;&nbsp; NO X</strong></p>


    <p>Para constancia de todo lo expuesto y convenido, las partes suscriben el presente Contrato de Adhesión, en la
        ciudad y fecha indicadas, en dos ejemplares de igual tenor y valor.</p>

    <p>La fecha de inscripción del modelo de contrato de adhesión que se utiliza, es:
        <strong>
            {{ $datosPrestador['ciudad_fecha'] ?? 'GUAYAQUIL' }},
            {{ mb_strtoupper(\Carbon\Carbon::now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY'), 'UTF-8') }}

        </strong>
    </p>

    <table style="width: 100%; text-align: center; border: none; border-collapse: collapse; margin-top: 30px;">
        <tr>
            <!-- Firma del Prestador -->
            <td style="padding: 10px; border: none;">
                <img src="{{ public_path('images/FJORGE1.bmp') }}" alt="Firma del prestador"
                    style="width: 130px; height: auto; margin-bottom: 10px; border: none;"><br>
                <strong>EL PRESTADOR</strong><br>
                SERVICIO DE OFICINA COMPUTARIZADO<br>
                SEROFICOM S.A.<br>
                RUC N°. 0993238155001<br><br>
            </td>

            <!-- Firma del Cliente -->
            <td style="padding: 10px; border: none;">
                <img src="{{ public_path('storage/' . $cliente->firma) }}" alt="Firma del cliente"
                    style="width: 130px; height: auto; margin-bottom: 10px; border: none;"><br>
                <strong>EL ABONADO</strong><br>
                NOMBRES Y APELLIDOS: {{ mb_strtoupper($cliente->nombres . ' ' . $cliente->apellidos) }}<br>
                CÉDULA: {{ $cliente->identificacion }}<br><br>
            </td>
        </tr>
    </table>



    @if(isset($authDebito) && $authDebito['mostrar'])
        <div style="page-break-before: always;"></div>

        <!-- COVER PAGE OVERLAY: Tapar marca de agua y headers anteriores -->
        <div
            style="position: absolute; top: -100px; left: -40px; width: 216mm; height: 115%; background: white; z-index: 10000; overflow: hidden;">

            <!-- ENCABEZADO PERSONALIZADO -->
            <img src="{{ public_path('images/encabezado_debito_credito.png') }}"
                style="width: 100%; height: auto; display: block;">

            <!-- PIE DE PAGINA PERSONALIZADO -->
            <img src="{{ public_path('images/piepagina_debito_credito.png') }}"
                style="position: absolute; bottom: 0; left: 0; width: 100%; height: auto; display: block;">

            <!-- CONTENIDO DEL ANEXO -->
            <div style="padding: 20px 40px; margin-top: 10px;">


                <p style="text-align: center; font-weight: bold; font-size: 14px; margin-bottom: 20px;">
                    ANEXO 1<br>
                    AUTORIZACIÓN PARA DÉBITO AUTOMÁTICO POR CONCEPTO DE PAGO DE SERVICIOS
                </p>

                <p>Fecha: <span style="text-decoration: underline;">
                        {{ $datosPrestador['ciudad_fecha'] ?? 'GUAYAQUIL' }},
                        {{ mb_strtoupper(\Carbon\Carbon::now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY'), 'UTF-8') }}
                    </span></p>

                <p><strong>I. DATOS DEL CLIENTE Marcar la Opción Aplicable:</strong><br>
                    [ {{ strlen($authDebito['identificacion']) < 13 ? 'X' : ' ' }} ] Persona Natural &nbsp;&nbsp;&nbsp;
                    [ {{ strlen($authDebito['identificacion']) >= 13 ? 'X' : ' ' }} ] Persona Jurídica
                </p>

                <p>
                    <strong>A. DATOS DE PERSONA NATURAL</strong><br>
                    Nombre Completo: <span
                        style="border-bottom: 1px solid #000; padding-right: 200px;">{{ $authDebito['titular'] }}</span><br>
                    Cédula de Identidad (CI): <span
                        style="border-bottom: 1px solid #000; padding-right: 150px;">{{ $authDebito['identificacion'] }}</span>
                </p>

                <p>
                    <strong>B. DATOS DE PERSONA JURÍDICA</strong><br>
                    Razón Social (Empresa): <span
                        style="border-bottom: 1px solid #000; padding-right: 200px;">__________________________________________________</span><br>
                    R.U.C.: <span
                        style="border-bottom: 1px solid #000; padding-right: 150px;">___________________________</span><br>
                    Representante Legal: <span
                        style="border-bottom: 1px solid #000; padding-right: 200px;">_________________________________________________</span>
                </p>

                <p>
                    <strong>II. DATOS DE LA CUENTA O TARJETA PARA DÉBITO AUTORIZO EXPRESAMENTE A SERVICIO DE OFICINA
                        COMPUTARIZADO SEROFICOM SA a debitar de la siguiente cuenta/tarjeta el valor de los servicios
                        contratados.</strong>
                </p>

                <p>
                    TIPO DE INSTRUMENTO (Marcar uno):<br>
                    [
                    {{ ($authDebito['tipo'] == 'DEBITO_BANCARIO' && (in_array(strtoupper($authDebito['tipo_cuenta'] ?? ''), ['AHORROS', 'AHORRO']))) ? 'X' : ' ' }}
                    ] CUENTA DE AHORROS &nbsp;&nbsp;
                    [
                    {{ ($authDebito['tipo'] == 'DEBITO_BANCARIO' && (in_array(strtoupper($authDebito['tipo_cuenta'] ?? ''), ['CORRIENTE', 'CORRIENTES']))) ? 'X' : ' ' }}
                    ] CUENTA CORRIENTE &nbsp;&nbsp;
                    [ {{ $authDebito['tipo'] == 'TARJETA_CREDITO' ? 'X' : ' ' }} ] TARJETA DE CRÉDITO
                </p>

                <p>
                    DATOS DEL INSTRUMENTO:<br>
                    Número de Cuenta/Tarjeta: <span style="border-bottom: 1px solid #000; padding-right: 100px;">
                        {{ $authDebito['numero'] ?: '________________________' }}
                    </span><br>
                    Fecha de Expiración (Solo tarjetas, MM/AA): <span
                        style="border-bottom: 1px solid #000; padding-right: 50px;">
                        {{ $authDebito['expiracion'] ?: '__________' }}
                    </span><br>
                    Institución Financiera: <span style="border-bottom: 1px solid #000; padding-right: 150px;">
                        {{ $authDebito['banco'] ?: '___________________________________' }}
                    </span>
                </p>

                <p>
                    DATOS DEL TITULAR (Si es diferente al firmante):<br>
                    Titular de la Cuenta/Tarjeta: <span
                        style="border-bottom: 1px solid #000; padding-right: 200px;">__________________________________________________</span><br>
                    Cédula/RUC del Titular: <span
                        style="border-bottom: 1px solid #000; padding-right: 150px;">_____________________________</span>
                </p>

                <p style="text-align: justify; font-size: 10px;">
                    <strong>III. TÉRMINOS Y CONDICIONES</strong> 1. <strong>CONCEPTO DEL DÉBITO:</strong> El débito se
                    realizará
                    por concepto de todos los valores estipulados en el Contrato de Servicios firmado entre el Cliente y
                    SERVICIO DE OFICINA COMPUTARIZADO SEROFICOM SA. 2. <strong>OBLIGACIÓN DEL CLIENTE:</strong> El Cliente
                    se
                    compromete a mantener los fondos suficientes y disponibles en la cuenta o tarjeta indicada para cubrir
                    el
                    valor total del pago en la fecha de procesamiento. 3. <strong>REFERENCIA:</strong> Al acreditar al
                    beneficiario, se deberá mencionar como referencia lo siguiente: PAGO EFECTUADO A SEROFICOM SA POR
                    SERVICIOS.
                    4. <strong>ACEPTACIÓN:</strong> La presente autorización estará vigente hasta que el Cliente la revoque
                    por
                    escrito. — <strong>IV. FIRMA DE AUTORIZACIÓN</strong> Con mi firma, declaro que toda la información
                    proporcionada es veraz y autorizo los débitos recurrentes bajo los términos antes descritos.
                </p>

                <br>

                <p style="margin-bottom: 5px;">
                    CÉDULA DE IDENTIDAD / R.U.C. <span
                        style="text-decoration: underline;">{{ $cliente->identificacion }}</span><br>
                    FIRMA DEL CLIENTE / REPRESENTANTE LEGAL
                </p>

                <div style="margin-top: 0px;">
                    <img src="{{ public_path('storage/' . $cliente->firma) }}" alt="Firma del cliente"
                        style="width: 130px; height: auto;">
                </div>

                <br><br><br> {{-- Extra visual space buffer at end of content --}}

            </div>
        </div> <!-- Close Cover Page Overlay -->
    @endif

    <div style="page-break-before: always;"></div> <!-- CAMBIA A NUEVA PÁGINA (ANEXO 1F) -->

    <table
        style="width: 100%; border-collapse: collapse; margin-top: 20px; text-align: center; font-family: 'Times New Roman'; font-size: 12px;">
        <tr>
            <td style="border: 1px solid #000; padding: 8px;" colspan="2"><strong>ANEXO 1F</strong></td>
        </tr>
        <tr>
            <td style="border: 1px solid #000; padding: 8px;" colspan="2">
                <strong>TÉCNICO – COMERCIAL QUE CONTIENE LAS “CONDICIONES PARTICULARES DEL SERVICIO”</strong>
            </td>
        </tr>
        <tr>
            <td style="border: 1px solid #000; padding: 8px;" colspan="2">
                <strong>FECHA DE SUSCRIPCIÓN DEL ANEXO: </strong>
                <span style="font-weight: bold;">
                    {{ $datosPrestador['ciudad_fecha'] ?? 'GUAYAQUIL' }},
                    {{ mb_strtoupper(\Carbon\Carbon::now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY'), 'UTF-8') }}
                </span>
            </td>
        </tr>
    </table>


    <table style="width: 100%; font-size: 12px; border-collapse: collapse; margin-top: 20px;">
        <tr>
            <td style="border: none;"><strong>Plan contratado:</strong></td>
            <td style="border: none;">{{ mb_strtoupper($plan->nombre_plan) }}</td>
        </tr>
        <tr>
            <td style="border: none;"><strong>Velocidad de subida:</strong></td>
            <td style="border: none;">{{ $plan->mb_subida }} Mbps</td>
        </tr>
        <tr>
            <td style="border: none;"><strong>Velocidad de bajada:</strong></td>
            <td style="border: none;">{{ $plan->mb_bajada }} Mbps</td>
        </tr>
        <tr>
            <td style="border: none;"><strong>Precio mensual:</strong></td>
            <td style="border: none;">${{ number_format($plan->precio, 2) }} (incluye IVA)</td>
        </tr>

        {{-- Campos adicionales solicitados --}}
        <tr>
            <td style="border: none;"><strong>Establecimiento:</strong></td>
            <td style="border: none;">{{ $cliente->establecimiento }}</td>
        </tr>
        <tr>
            {{-- Sentence Case LABEL --}}
            <td style="border: none;"><strong>Tipo de cuenta:</strong></td>
            <td style="border: none;">
                Residencial
            </td>
        </tr>
        <tr>
            {{-- Sentence Case LABEL --}}
            <td style="border: none;"><strong>Red de acceso:</strong></td>
            <td style="border: none;">Fibra óptica</td>
        </tr>
        <tr>
            {{-- Sentence Case LABEL --}}
            <td style="border: none;"><strong>Nivel de compartición:</strong></td>
            <td style="border: none;">
                {{-- UPPERCASE VALUE --}}
                {{ mb_strtoupper($cliente->nivel_comparticion) }}
            </td>
        </tr>
        <tr>
            <td style="border: none;"><strong>Forma de pago:</strong></td>
            <td style="border: none;">
                {{ mb_strtoupper($cliente->metodoPago->nombre ?? $cliente->metodo_pago_texto ?? 'NO DEFINIDO') }}
            </td>
        </tr>

        {{-- CONDICIONAL TERCERA EDAD / DISCAPACIDAD: Mostrar Servicios Adicionales (Zapping) --}}
        {{-- SERVICIOS ADICIONALES (Dinámico) --}}
        @if($cliente->serviciosAdicionales && $cliente->serviciosAdicionales->count() > 0)
            <tr>
                <td style="border: none; vertical-align: top;"><strong>Servicios Adicionales:</strong></td>
                <td style="border: none;">
                    @foreach($cliente->serviciosAdicionales as $servicio)
                        {{ $servicio->nombre }} ($ {{ number_format($servicio->precio, 2) }} + IVA)<br>
                    @endforeach
                </td>
            </tr>
        @endif

    </table>


    <p style="font-size: 8px; line-height: 1.6; color: #374151; margin-top: 15px; text-align: justify;">
        <strong>Términos y condiciones:</strong> La aplicación de promociones y planes está sujeta a la obligatoriedad
        de que el método de pago en efectivo
        no
        aplica para ningún descuento en la tarifa mensual, aunque sí podrá optar a la Instalación Gratuita; esta
        instalación gratuita conlleva un compromiso de permanencia mínima de 1 año (12 meses) para los planes Base,
        Plus
        y Ultra, y una permanencia mínima de 2 años (24 meses) para los planes Ciudadano y Beneficio Doble; la
        cancelación anticipada generará un cargo único por salida equivalente al costo total de la instalación,
        fijado
        en $150 (más IVA); para el Plan Beneficio Doble, el descuento se aplicará y mantendrá solo mientras ambos
        planes
        permanezcan activos bajo el mismo titular; finalmente, el 50% de descuento por Tercera Edad o Discapacidad
        aplica sobre la tarifa base, pero no incluye el servicio de Zapping TV, el cual está incluido únicamente en
        los
        Planes Base, Plus y Ultra.
    </p>


    <!-- Firmas -->
    <table style="width: 100%; text-align: center; border: none; border-collapse: collapse; margin-top: 30px;">
        <tr>
            <!-- Firma del Prestador -->
            <td style="padding: 10px; border: none;">
                <img src="{{ public_path('images/FJORGE1.bmp') }}" alt="Firma del prestador"
                    style="width: 130px; height: auto; margin-bottom: 10px; border: none;"><br>
                <strong>EL PRESTADOR</strong><br>
                SERVICIO DE OFICINA COMPUTARIZADO<br>
                SEROFICOM S.A.<br>
                RUC N°. 0993238155001<br><br>
            </td>


            <!-- Firma del Cliente -->
            <td style="padding: 10px; border: none;">
                <img src="{{ public_path('storage/' . $cliente->firma) }}" alt="Firma del cliente"
                    style="width: 130px; height: auto; margin-bottom: 10px; border: none;"><br>
                <strong>EL ABONADO</strong><br>
                NOMBRES Y APELLIDOS: {{ mb_strtoupper($cliente->nombres . ' ' . $cliente->apellidos) }}<br>
                CÉDULA: {{ $cliente->identificacion }}<br><br>
            </td>
        </tr>
    </table>


    <div style="page-break-before: always;"></div> <!-- NUEVA PÁGINA -->

    <table style="width: 100%; border-collapse: collapse; text-align: center; font-size: 10px; margin-bottom: 11px;">
        <tr>
            <td style="border: 1px solid #000; padding: 6px;" colspan="2"><strong>ANEXO 2</strong></td>
        </tr>
        <tr>
            <td style="border: 1px solid #000; padding: 6px;" colspan="2"><strong>USO DE INFORMACIÓN
                    PERSONAL.</strong></td>
        </tr>
        <tr>
            <td style="border: 1px solid #000; padding: 6px;" colspan="2">
                <strong>FECHA DE SUSCRIPCIÓN DEL ANEXO: </strong>
                <span style="font-weight: bold;">
                    {{ $datosPrestador['ciudad_fecha'] ?? 'GUAYAQUIL' }},
                    {{ mb_strtoupper(\Carbon\Carbon::now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY'), 'UTF-8') }}
                </span>
            </td>
        </tr>
    </table>

    <p style="text-align: justify; font-size: 10px; margin-top: 10px;">
        A través de la suscripción del presente documento que es parte integrante del CONTRATO DE ADHESIÓN autorizo
        y
        doy mi consentimiento expreso para que la empresa SERVICIO DE OFICINA COMPUTARIZADO SEROFICOM S.A., con RUC
        0993238155001, con domicilio en la provincia de SANTA ELENA, cantón SANTA ELENA, parroquia SAN JOSÉ DE
        ANCÓN,
        dirección Barrio Guayaquil #430, correo electrónico <a href="mailto:info@seroficom.org">info@seroficom.org</a>,
        página <a href="https://www.seroficom.org">www.seroficom.org</a>, utilice mi información personal
        correspondiente a:
    </p>

    <table style="width: 100%; font-size: 10px; border-collapse: collapse; margin: 1px 0;">
        <tr>
            <td style="border: 1px solid black; font-weight: bold; background-color: #f0f0f0;">NOMBRES COMPLETOS
            </td>
            <td style="border: 1px solid black;">{{ $cliente->nombres }} {{ $cliente->apellidos }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid black; font-weight: bold; background-color: #f0f0f0;">CÉDULA DE IDENTIDAD
            </td>
            <td style="border: 1px solid black;">{{ $cliente->identificacion }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid black; font-weight: bold; background-color: #f0f0f0;">NACIONALIDAD</td>
            <td style="border: 1px solid black;">ECUATORIANA</td>
        </tr>
        <tr>
            <td style="border: 1px solid black; font-weight: bold; background-color: #f0f0f0;">DIRECCIÓN COMPLETA
            </td>
            <td style="border: 1px solid black;">{{ $cliente->direccion }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid black; font-weight: bold; background-color: #f0f0f0;">TELÉFONO</td>
            <td style="border: 1px solid black;">
                {{ empty($cliente->telefonos) ? 'No registrado' : (is_array($cliente->telefonos) ? implode(', ', $cliente->telefonos) : $cliente->telefonos) }}
            </td>
        </tr>
        <tr>
            <td style="border: 1px solid black; font-weight: bold; background-color: #f0f0f0;">CORREO ELECTRÓNICO
            </td>
            <td style="border: 1px solid black;">
                {{ empty($cliente->correos) ? 'No registrado' : (is_array($cliente->correos) ? implode(', ', $cliente->correos) : $cliente->correos) }}
            </td>
        </tr>
    </table>


    <p style="font-size: 10px; margin-top: 15px;">Antes consignada para:</p>
    <ol style="font-size: 10px; padding-left: 20px;">
        <li>La promoción comercial de servicio y productos.</li>
        <li>Notificar cambios relacionados con los términos y condiciones del presente Contrato.</li>
        <li>Realizar gestiones de cobranzas y demás promociones aplicables.</li>
        <li>Dar a conocer cualquier morosidad y de cualquier otro cobro que fuere procedente en conformidad al
            Contrato,
            a Servicios Equifax Ecuador, para ser incorporada en sus registros y bases de datos e informadas a
            terceros.
        </li>
        <li>Comunicaciones relacionadas a soporte y notificaciones de carácter técnico.</li>
    </ol>

    <p style="font-size: 10px; margin-top: 15px;">
        La presente autorización tiene un plazo de duración de
        <span style="font-weight: bold;">12 MESES</span>
        contados a partir de la fecha de suscripción de la misma. En cualquier momento, el abonado podrá revocar su
        consentimiento, sin que el prestador pueda condicionar o establecer requisitos para tal fin, adicionales a
        la
        simple voluntad del abonado.
    </p>

    <!-- Firma -->
    <table style="width: 100%; text-align: left; margin-top: 40px; font-size: 10px; border-collapse: collapse;">
        <tr>
            <td style="padding: 0; border: none;">
                <img src="{{ public_path('storage/' . $cliente->firma) }}" alt="Firma del cliente"
                    style="width: 130px; height: auto; margin-bottom: 10px;"><br>
                <strong>EL ABONADO</strong><br>
                {{ mb_strtoupper($cliente->nombres . ' ' . $cliente->apellidos) }}<br>
                {{ $cliente->identificacion }}<br>
            </td>
        </tr>
    </table>

</body>

</html>