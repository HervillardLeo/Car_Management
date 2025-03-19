import "./styles/app.css";

import $ from "jquery";

$(async function () {
    let selectedClient = $('#client-selector').val();
    displayModuleSelector(selectedClient);
    
    await loadModule(selectedClient === 'clientb' ? $("#module-selector").val() : 'cars');

    $("#client-selector").on("change", function (e) {
        changeClient(e.target.value);
    });
    
    $("#module-selector").on("change", function () {
        $(".dynamic-div").html("");
        loadModule($(this).val());
    });
});

async function loadContent() {
    var module = $(".dynamic-div").data("module");
    if ($('#client-selector').val() === 'clientb') {
        module = $("#module-selector").val();
    }
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
    await fetch("/change-client", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded",
        },
        body: new URLSearchParams({ client: clientId }),
    });

    displayModuleSelector(clientId);
    await loadModule(clientId === 'clientb' ? $("#module-selector").val() : 'cars');
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
            $(".dynamic-div").html(data);
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

function loadGarages() {
    fetch("/load-garages", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded",
        },
    })
        .then(response => response.text())
        .then(data => {
            $(".dynamic-div").append(data);
            addGarageDetailListener();
        })
        .catch(error => console.error("Erreur lors du chargement des garages :", error));
}

async function loadModule(module) {
    await loadContent();
    if (module === 'cars') {
        loadCars();
    } else if (module === 'garages') {
        loadGarages();
    }
}

function displayModuleSelector(client) {
    if (client === "clientb") {
        $("#module").css('display', 'block');
    } else {
        $("#module").css('display', 'none');
    }
}

function loadGarageDetail(garageId) {
    fetch(`/garage-details/${garageId}`, {
        method: "GET"
    })
        .then(response => response.text())
        .then(data => {
            $(".dynamic-div").html(data);
            $(".goback").on("click", async function () {
                $(".dynamic-div").html("");
                await loadContent();
                loadGarages();
            });
        })
        .catch(error => console.error("Erreur lors du chargement des voitures :", error));
}

function addGarageDetailListener() {
    $(".click-garage").on("click", function () {
        loadGarageDetail($(this).data("garage-id"));
    });
}
