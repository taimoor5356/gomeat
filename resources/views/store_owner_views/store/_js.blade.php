<script>
    $(document).ready(function() {
        $(document).on('change', '#module_id', function() {
            let _this = $(this);
            alert(_this.val());
        });

        // Handle file selection
        $("#file-input").on("change", function() {
            var file = this.files[0];
            if (file) {
                // Display the selected image in the clicked image container
                displayImageInContainer(file, $("#upload-image"));
                // You can also perform additional checks or actions here before uploading
                uploadFile(file);

                // Reset the file input to allow selecting the same file again
                $(this).val("");
            }
        });

        function displayImageInContainer(file, container) {
            // Use FileReader to read the selected image and display it
            var reader = new FileReader();

            reader.onload = function(e) {
                container.attr("src", e.target.result);
            };

            reader.readAsDataURL(file);
        }
    });
</script>