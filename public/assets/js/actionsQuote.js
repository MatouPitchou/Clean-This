$(document).ready(function () {
  function updateOperationStatus(operationId, newQuote, newStatus, callback) {
    var url = "/operations/" + operationId + "/edit";
    $.ajax({
      url: url,
      method: "PUT",
      contentType: "application/json",
      data: JSON.stringify({ quote: newQuote, status: newStatus }),
      success: function(data) {
        callback(data, operationId, newStatus);
      },
      error: function(textStatus, errorThrown) {
        console.error("Erreur lors de la requête AJAX:", textStatus, errorThrown);
      }
    });
  }

  function handleQuoteAction(button, operationId, newQuote, newStatus, confirmationMessage) {
    if (confirm(confirmationMessage)) {
      updateOperationStatus(operationId, newQuote, newStatus, function(data, operationId) {
        var statusCell = $('.orderQuote_' + operationId);
        var newContent = "";
        switch(newStatus) {
          case "Disponible":
            newContent = '<span class="status processQuote">Devis accepté</span>';
            break;
          case "Annulé":
            newContent = '<span class="status refusedQuote">Devis refusé</span>';
            break;
          default:
            newContent = '<span class="status">' + newStatus + '</span>';
        }

        statusCell.html(newContent);

        $('.acceptQuoteBtn[data-operation-id="' + operationId + '"]').hide();
        $('.refuseQuoteBtn[data-operation-id="' + operationId + '"]').hide();
      });
    }
  }

  $(".acceptQuoteBtn").on("click", function() {
    var operationId = $(this).data("operation-id");
    handleQuoteAction(this, operationId, "Validé", "Disponible", "Voulez-vous vraiment accepter ce devis ?");
  });

  $(".refuseQuoteBtn").on("click", function() {
    var operationId = $(this).data("operation-id");
    handleQuoteAction(this, operationId, "Refusé", "Annulé", "Souhaitez-vous vraiment refuser ce devis ?");
  });
});
