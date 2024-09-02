$(document).ready(function () {
  // Requête AJAX pour récupérer la locale de la session
  $.ajax({
    url: "/get-locale",
    type: "GET",
    success: function (response) {
      var locale = response.message;
      var urlLanguage = '';
      if (locale === "en") {
        urlLanguage = "//cdn.datatables.net/plug-ins/2.0.3/i18n/en-GB.json";
      } else 
      urlLanguage = "//cdn.datatables.net/plug-ins/2.0.3/i18n/fr-FR.json";

      // Initialiser DataTables avec la langue récupérée
      $("#operations").DataTable({
        order: [[0, "desc"]],
        language: {
          url: urlLanguage,
        },
      });

      $("#orders").DataTable({
        order: [[0, "desc"]],
        language: {
          url: urlLanguage,
        },
      });
    },
    error: function (xhr, status, error) {
      console.error(
        "Erreur lors de la récupération de la locale de la session :",
        error
      );
    },
  });
});

