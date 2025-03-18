import "./styles/app.css";

import $ from "jquery";

$(function () {
  loadContent();
  loadCars()
  $("#client-selector").on("change", function(e) {
      changeClient(e.target.value);
  });
});

function loadContent() {
  var module = $(".dynamic-div").data("module");
  var script = $(".dynamic-div").data("script");

  fetch("/load-content", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: new URLSearchParams({ module: module, script: script }),
  })
    .then((response) => response.text())
    .then((data) => {
      $(".dynamic-div").append(data);
    })
    .catch((error) => console.error("Erreur de chargement :", error));
}

function changeClient(clientId) {
  $(".dynamic-div").html("");
  fetch("/change-client", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: new URLSearchParams({ client: clientId }),
  })
  .then(() => loadContent())
  .then(() => setTimeout(loadCars(), 50));//On attends que le cookie soit mis a jour
}

function loadCars() {
  fetch("/load-cars", {
      method: "POST"
  })
  .then(response => response.text())
  .then(data => {
      $(".dynamic-div").append(data);
  })
  .catch(error => console.error("Erreur lors du chargement des voitures :", error));
}