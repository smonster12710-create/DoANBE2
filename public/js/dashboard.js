document.addEventListener("DOMContentLoaded", function () {

    const input = document.getElementById("postImages");
    const preview = document.getElementById("preview-images");

    if (!input || !preview) return;

    let selectedFiles = [];

    input.addEventListener("change", function (e) {

        const newFiles = Array.from(e.target.files);d

        // thêm file mới vào mảng cũ
        selectedFiles = [...selectedFiles, ...newFiles];

        // render lại preview
        renderPreview();

        // cập nhật lại input files
        updateInputFiles();

    });

    function renderPreview() {

        preview.innerHTML = "";

        selectedFiles.forEach((file, index) => {

            const reader = new FileReader();

            reader.onload = function (event) {

                const wrapper = document.createElement("div");

                wrapper.style.position = "relative";

                const img = document.createElement("img");

                img.src = event.target.result;

                img.style.width = "100px";
                img.style.height = "100px";
                img.style.objectFit = "cover";
                img.style.borderRadius = "10px";

                // nút xoá
                const removeBtn = document.createElement("button");

                removeBtn.innerHTML = "×";

                removeBtn.style.position = "absolute";
                removeBtn.style.top = "0";
                removeBtn.style.right = "0";
                removeBtn.style.background = "red";
                removeBtn.style.color = "white";
                removeBtn.style.border = "none";
                removeBtn.style.borderRadius = "50%";
                removeBtn.style.width = "22px";
                removeBtn.style.height = "22px";
                removeBtn.style.cursor = "pointer";

                removeBtn.addEventListener("click", function () {

                    selectedFiles.splice(index, 1);

                    renderPreview();

                    updateInputFiles();
                });

                wrapper.appendChild(img);
                wrapper.appendChild(removeBtn);

                preview.appendChild(wrapper);
            };

            reader.readAsDataURL(file);
        });
    }

    function updateInputFiles() {

        const dataTransfer = new DataTransfer();

        selectedFiles.forEach(file => {
            dataTransfer.items.add(file);
        });

        input.files = dataTransfer.files;
    }

});