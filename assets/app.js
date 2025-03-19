import "./styles/app.css";

import $ from "jquery";

$(async function () {
    await loadContent();
    loadCars();
    $("#client-selector").on("change", function (e) {
        changeClient(e.target.value);
    });
});

async function loadContent() {
    var module = $(".dynamic-div").data("module");
    var script = $(".dynamic-div").data("script");

    await fetch("/load-content", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded",
        },
        body: new URLSearchParams({ module: module, script: script }),
    })
        .then((response) => response.text())
        .then((data) => {
            $(".dynamic-div").prepend(data);
        })
        .catch((error) => console.error("Erreur de chargement :", error));
}

async function changeClient(clientId) {
    $(".dynamic-div").html("");
    fetch("/change-client", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded",
        },
        body: new URLSearchParams({ client: clientId }),
    })
        .then(async () => {
            await loadContent();
            loadCars();
        })
}

function loadCars() {
    fetch("/load-cars", {
        method: "POST"
    })
        .then(response => response.text())
        .then(data => {
            $(".dynamic-div").append(data);
            addCarDetailListener();
        })
        .catch(error => console.error("Erreur lors du chargement des voitures :", error));
}

function loadCarDetail(carId) {
    fetch(`/car-details/${carId}`, {
        method: "GET"
    })
        .then(response => response.text())
        .then(data => {
            $(".dynamic-div").html("");
            $(".dynamic-div").append(data);
            $(".goback").on("click", async function () {
                $(".dynamic-div").html("");
                await loadContent();
                loadCars();
            });
        })
        .catch(error => console.error("Erreur lors du chargement des voitures :", error));
}

function addCarDetailListener() {
    $(".click-car").on("click", function () {
        loadCarDetail($(this).data("car-id"));
    });
}