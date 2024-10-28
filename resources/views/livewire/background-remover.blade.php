<!-- --------Main section-------- -->
<div class="container mx-auto px-4 py-6">
    <h2 class="text-center text-blue-600 text-xl md:text-2xl font-semibold mb-6">Background remover</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 md:gap-x-2 justify-items-center pt-5">
        <div class="flex justify-between items-center p-1 h-96 w-full rounded bg-gray-100">

            <!-- ---------Input section------- -->
            <div
                class="flex flex-col justify-between items-center h-full w-full bg-white border-dashed border-2 border-gray-200 shadow-inner rounded">
                <!-- ------------------Input zone-------------- -->
                <div class="h-full w-full rounded">
                    <!-- ------------------Webcam zone-------------- -->
                    <div id="webcamZone" class="hidden h-full w-full"></div>
                    <div id="fileDropZone" class="h-full w-full">
                        <label for="dropzone-file"
                            class="flex flex-col items-center justify-center h-full w-full cursor-pointer hover:bg-gray-200">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <svg class="w-8 h-8 mb-4 text-gray-500" aria-hidden="true"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2" />
                                </svg>
                                <p class="mb-2 text-sm text-gray-500"><span class="font-semibold">Click to upload</span>
                                    or drag and
                                    drop</p>
                                <p class="text-xs text-gray-500">SVG, PNG, JPG or GIF (MAX. 800x400px)</p>
                            </div>
                            <input id="dropzone-file" type="file" wire:model="image" class="hidden" />
                        </label>
                    </div>
                </div>
                <div
                    class="flex justify-center space-x-2 py-2 w-full bg-white border-solid border-t-2 border-gray-200 ">
                    <button id="activateCameraButton" class="text-gray-500 hover:text-amber-700 px-4 py-2 rounded">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                        </svg>

                    </button>
                    <button onclick="take_snapshot()" id="captureButton"
                        class="text-gray-500 hover:text-amber-600 px-4 py-2 rounded hidden">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                        </svg>

                    </button>
                    <button id="fileUploadButton" class="text-gray-500 hover:text-amber-600 px-4 py-2 rounded">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                        </svg>

                    </button>
                </div>
            </div>
        </div>

        <!-- -----Result section------- -->
        <div class="flex justify-center items-center p-1 h-96 w-full rounded bg-gray-100">
            <div
                class="flex items-center justify-center h-full w-full rounded bg-white border-solid border-2 border-gray-200 shadow-inner">
                @if($maskedImageUrl)
                    <img id="masked-image" src="{{ asset($maskedImageUrl) }}" alt="Uploaded image"
                        class="object-contain max-w-full max-h-full" />
                @endif
                @if($isProcessing)
                    <div class="flex items-center justify-center w-full h-full">
                        <div
                            class="px-3 py-1 text-xs font-medium leading-none text-center text-blue-800 bg-blue-200 rounded-full animate-pulse dark:bg-blue-900 dark:text-blue-200">
                            processing...</div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script language="JavaScript">
        const activateCameraButton = document.getElementById('activateCameraButton');
        const captureButton = document.getElementById('captureButton');
        const fileUploadButton = document.getElementById('fileUploadButton');
        const fileDropZone = document.getElementById('fileDropZone');
        const webcamZone = document.getElementById('webcamZone');

        //Webcam setup
        Webcam.set({
            //width: 320,
            //height: 240,
            image_format: 'png',
            jpeg_quality: 100,
            // dest_width: 640,
            // dest_height: 480,
            // force_flash: false,
            // flip_horiz: true,
            // fps: 45
        });

        // Function to activate the camera on button click
        activateCameraButton.addEventListener('click', function () {
            if (!fileDropZone.classList.contains('hidden')) {
                fileDropZone.classList.add('hidden');
            }

            if (webcamZone.classList.contains('hidden')) {
                webcamZone.classList.remove('hidden');
            }

            if (!activateCameraButton.classList.contains('hidden')) {
                activateCameraButton.classList.add('hidden');
            }

            if (captureButton.classList.contains('hidden')) {
                captureButton.classList.remove('hidden');
            }

            Webcam.attach('#webcamZone');
        });


        // Turn off the camera without removing the DOM element
        function turnOffCamera() {
            if (Webcam.stream) {
                Webcam.stream.getTracks().forEach(track => track.stop());
            }
        }

        // Function to show file drop zone
        fileUploadButton.addEventListener('click', function () {
            turnOffCamera();

            if (!webcamZone.classList.contains('hidden')) {
                webcamZone.classList.add('hidden');
            }

            if (fileDropZone.classList.contains('hidden')) {
                fileDropZone.classList.remove('hidden');
            }
            if (activateCameraButton.classList.contains('hidden')) {
                activateCameraButton.classList.remove('hidden');
            }
            if (!captureButton.classList.contains('hidden')) {
                captureButton.classList.add('hidden');
            }
        })

        //Capture image
        function take_snapshot() {
            Webcam.snap(function (data_uri) {
                document.getElementById('webcamZone').innerHTML = '<img src="' + data_uri + '" class="object-contain max-w-full max-h-full"/>';
                turnOffCamera();

                var raw_image_data = data_uri.replace(/^data\:image\/\w+\;base64\,/, '');

                Livewire.dispatch("imageCaptured", { raw_data: raw_image_data });
            });

            if (activateCameraButton.classList.contains('hidden')) {
                activateCameraButton.classList.remove('hidden');
            }

            if (!captureButton.classList.contains('hidden')) {
                captureButton.classList.add('hidden');
            }
        }
    </script>
</div>