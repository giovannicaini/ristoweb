<div class="row">
    <div class="col-md-12">
        <div id="video-container">
            <video style="width:100%" id="qr-video"></video>
        </div>
        <div>
            <b>Seleziona fotocamera:</b>
            <select id="cam-list">
                <option value="environment" selected>Posteriore (default)</option>
                <option value="user">Frontale</option>
            </select>
        </div>
        <div>
            <button id="flash-toggle">📸 Flash: <span id="flash-state">off</span></button>
        </div>
        <br>
        <b>QR Code trovato: </b>
        <span id="cam-qr-result">Nessuno</span>
        <br>
        <b>Ultimo: </b>
        <span id="cam-qr-result-timestamp"></span>
        <br>
    </div>
</div>

<script type="module">
    import QrScanner from "{{ asset('js/qr-scanner/qr-scanner.min.js') }}";

    const video = document.getElementById('qr-video');
    const videoContainer = document.getElementById('video-container');
    const camList = document.getElementById('cam-list');
    const flashToggle = document.getElementById('flash-toggle');
    const flashState = document.getElementById('flash-state');
    const camQrResult = document.getElementById('cam-qr-result');
    const camQrResultTimestamp = document.getElementById('cam-qr-result-timestamp');
    
    function setResult(label, result) {
        console.log(result.data);
        label.textContent = result.data;
        camQrResultTimestamp.textContent = new Date().toString();
        label.style.color = 'teal';
        clearTimeout(label.highlightTimeout);
        label.highlightTimeout = setTimeout(() => label.style.color = 'inherit', 100);
    }
    function usaDati(result){
        alert(JSON.stringify(result));
    }
    // ####### Web Cam Scanning #######

    const scanner = new QrScanner(video, result => usaDati(result), {
        onDecodeError: error => {
            camQrResult.textContent = error;
            camQrResult.style.color = 'inherit';
        },
        highlightScanRegion: true,
        highlightCodeOutline: true,
        maxScansPerSecond: 5,
    });

    const updateFlashAvailability = () => {
        scanner.hasFlash().then(hasFlash => {
            camHasFlash.textContent = hasFlash;
            flashToggle.style.display = hasFlash ? 'inline-block' : 'none';
        });
    };

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

    QrScanner.hasCamera().then(hasCamera => camHasCamera.textContent = hasCamera);

    // for debugging
    window.scanner = scanner;

    document.getElementById('scan-region-highlight-style-select').addEventListener('change', (e) => {
        videoContainer.className = e.target.value;
        scanner._updateOverlay(); // reposition the highlight because style 2 sets position: relative
    });

    document.getElementById('show-scan-region').addEventListener('change', (e) => {
        const input = e.target;
        const label = input.parentNode;
        label.parentNode.insertBefore(scanner.$canvas, label.nextSibling);
        scanner.$canvas.style.display = input.checked ? 'block' : 'none';
    });

    document.getElementById('inversion-mode-select').addEventListener('change', event => {
        scanner.setInversionMode(event.target.value);
    });

    camList.addEventListener('change', event => {
        scanner.setCamera(event.target.value).then(updateFlashAvailability);
    });

    flashToggle.addEventListener('click', () => {
        scanner.toggleFlash().then(() => flashState.textContent = scanner.isFlashOn() ? 'on' : 'off');
    });

    document.getElementById('start-button').addEventListener('click', () => {
        scanner.start();
    });

    document.getElementById('stop-button').addEventListener('click', () => {
        scanner.stop();
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

    <script src="{{ asset('js/qr-scanner/qr-scanner.min.js') }}">
	<script>
		const qrScanner = new QrScanner(
			videoElem,
			result => console.log('decoded qr code:', result),
			{ /* your options or returnDetailedScanResult: true if you're not specifying any other options */ },
		);

    </script>

