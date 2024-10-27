<!-- --------Main section-------- -->
<div class="container mx-auto px-4 py-6">
    <h2 class="text-center text-blue-600 text-xl md:text-2xl font-semibold mb-6">Background remover</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 md:gap-x-2 justify-items-center pt-5">
        <div class="flex justify-between items-center p-1 h-96 w-full rounded bg-gray-100">

            <!-- ---------Webcam section------- -->
            <div
                class="flex flex-col justify-between items-center h-full w-full bg-white border-dashed border-2 border-gray-200 shadow-inner rounded">
                <div id="my_camera" class="h-full w-full rounded"></div>
                <div
                    class="flex justify-center space-x-2 py-2 w-full bg-white border-solid border-t-2 border-gray-200 ">
                    <button id="activateCameraButton"
                        class="bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded">Turn on camera</button>
                    <button onclick="take_snapshot()" id="captureButton"
                        class="bg-emerald-500 hover:bg-emerald-600 hidden text-white px-4 py-2 rounded">Capture</button>
                    <button id="fileUploadButton"
                        class="bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded">Upload</button>
                </div>
            </div>
        </div>

        <!-- -----Result section------- -->
        <div class="flex justify-center items-center p-1 h-96 w-full rounded bg-gray-100">
            <div class="h-full w-full rounded bg-white border-solid border-2 border-gray-200 shadow-inner">

            </div>

        </div>
    </div>

    <script language="JavaScript">
        const activateCameraButton = document.getElementById('activateCameraButton');
        const captureButton = document.getElementById('captureButton');

        //Webcam setup
        Webcam.set({
            //width: 320,
            //height: 240,
            image_format: 'jpeg',
            jpeg_quality: 90,
            // dest_width: 640,
            // dest_height: 480,
            // force_flash: false,
            // flip_horiz: true,
            // fps: 45
        });

        // Function to activate the camera on button click
        activateCameraButton.addEventListener('click', function () {
            // Hide the activate button and show the capture button
            activateCameraButton.style.display = 'none';
            //removeBackgroundButton.style.display = 'none';
            captureButton.style.display = 'inline-flex';

            // Hide img and activate webcam
            //cameraThumbnail.style.display = 'none';
            Webcam.attach('#my_camera');
        });

        // Turn off the camera without removing the DOM element
        function turnOffCamera() {
            if (Webcam.stream) {
                Webcam.stream.getTracks().forEach(track => track.stop());
            }
        }

        //Capture image
        function take_snapshot() {
            Webcam.snap(function (data_uri) {
                document.getElementById('my_camera').innerHTML = '<img src="' + data_uri + '"/>';
                turnOffCamera();

                var raw_image_data = data_uri.replace(/^data\:image\/\w+\;base64\,/, '');

                //document.getElementById('captured_image').value = raw_image_data;
                //document.getElementById('myform').submit();
            });

            activateCameraButton.style.display = 'inline-flex';
            captureButton.style.display = 'none';

        }
    </script>
</div>