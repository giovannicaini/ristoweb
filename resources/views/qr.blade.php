<div style="width:100%; font-size:60px!important;">
    <div style="width:100%">
        <div id="video-container">
            <video style="width:100%" id="qr-video"></video>
        </div>
        <button style="width:100%; height:200px; font-size:60px; border-raidus:10%; background-color:green;" id="apri-qr">Scansiona QR</button>
        <div>
            <b>Seleziona fotocamera:</b>
            <select style="width:100%; font-size:60px; height:200px;" id="cam-list">
                <option value="environment" selected>Posteriore (default)</option>
                <option value="user">Frontale</option>
            </select>
        </div>
        <div>
            <button id="flash-toggle">📸 Flash: <span id="flash-state">off</span></button>
        </div>
    </div>
</div>

<script type="module">
    import QrScanner from "{{ asset('js/qr-scanner/qr-scanner.min.js') }}";

    const video = document.getElementById('qr-video');
    const videoContainer = document.getElementById('video-container');
    const camList = document.getElementById('cam-list');
    
    function usaDati(result){
        scanner.stop();
        //alert(JSON.stringify(result));
        var uuid = result.data;
        if (uuid){
            var xhr = new XMLHttpRequest();
            var url = "https://feste.parrocchiasantateresa.it/qr/"+uuid;
            //alert(url);
            xhr.open('GET', url);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhr.overrideMimeType("text");
            xhr.onreadystatechange = function () {
                var DONE = 4; // stato 4 indica che la richiesta è stata effettuata.
                var OK = 200; // se la HTTP response ha stato 200 vuol dire che ha avuto successo.
                if (xhr.readyState === DONE) {
                    if (xhr.status === OK) {
                    alert(xhr.responseText); // Questo è il corpo della risposta HTTP
                    } 
                    else {
                    alert('Errore: ' + xhr.status); // Lo stato della HTTP response.
                    }
                    window.location.reload();
                }
            };
            // Invia la richiesta a server-side.php
            xhr.send(null);
        }
    }
    // ####### Web Cam Scanning #######

    const scanner = new QrScanner(video, result => usaDati(result), {
        highlightScanRegion: true,
        highlightCodeOutline: true,
        maxScansPerSecond: 5,
    });

    scanner.start().then(() => {
        //updateFlashAvailability();
        // List cameras after the scanner started to avoid listCamera's stream and the scanner's stream being requested
        // at the same time which can result in listCamera's unconstrained stream also being offered to the scanner.
        // Note that we can also start the scanner after listCameras, we just have it this way around in the demo to
        // start the scanner earlier.
        QrScanner.listCameras(true).then(cameras => cameras.forEach(camera => {
            const option = document.createElement('option');
            option.value = camera.id;
            option.text = camera.label;
            camList.add(option);
        }));
    });

    // for debugging
    window.scanner = scanner;

    document.getElementById('apri-qr').addEventListener('click', event => {
        scanner.start().then(() => {
        updateFlashAvailability();
        // List cameras after the scanner started to avoid listCamera's stream and the scanner's stream being requested
        // at the same time which can result in listCamera's unconstrained stream also being offered to the scanner.
        // Note that we can also start the scanner after listCameras, we just have it this way around in the demo to
        // start the scanner earlier.
        QrScanner.listCameras(true).then(cameras => cameras.forEach(camera => {
            const option = document.createElement('option');
            option.value = camera.id;
            option.text = camera.label;
            camList.add(option);
        }));
    });
    });

    camList.addEventListener('change', event => {
        scanner.setCamera(event.target.value).then(updateFlashAvailability);
    });

       // ####### File Scanning #######

</script>

<style>
    div {
        margin-bottom: 16px;
    }

    #video-container {
        line-height: 0;
		width:100%:
    }

    #video-container.example-style-1 .scan-region-highlight-svg,
    #video-container.example-style-1 .code-outline-highlight {
        stroke: #64a2f3 !important;
    }

    #video-container.example-style-2 {
        position: relative;
        width: 100%;
        height: 100%;
        overflow: hidden;
    }
    #video-container.example-style-2 .scan-region-highlight {
        border-radius: 30px;
        outline: rgba(0, 0, 0, .25) solid 50vmax;
    }
    #video-container.example-style-2 .scan-region-highlight-svg {
        display: none;
    }
    #video-container.example-style-2 .code-outline-highlight {
        stroke: rgba(255, 255, 255, .5) !important;
        stroke-width: 15 !important;
        stroke-dasharray: none !important;
    }

    #flash-toggle {
        display: none;
    }

    hr {
        margin-top: 32px;
    }
    input[type="file"] {
        display: block;
        margin-bottom: 16px;
    }
</style>

	<!-- /.row -->
<!--
    <script src="{{ asset('js/qr-scanner/qr-scanner.min.js') }}">
	<script>
		const qrScanner = new QrScanner(
			videoElem,
			result => console.log('decoded qr code:', result),
			{ /* your options or returnDetailedScanResult: true if you're not specifying any other options */ },
		);

    </script>
-->
