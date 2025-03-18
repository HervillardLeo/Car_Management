import "./styles/app.css";

import $ from "jquery";

$(function () {
  loadContent();
  $("#client-selector").on("change", (e) => changeClient(e.target.value));
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
      $(".dynamic-div").innerHTML = data;
    })
    .catch((error) => console.error("Erreur de chargement :", error));
}

function changeClient(clientId) {
  fetch("/change-client", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: new URLSearchParams({ client: clientId }),
  }).then(() => {
    loadContent();
  });
}
