export function formatRole(role) {
  // Supposons que ROLE_ADMIN doit être formaté comme "Admin"
  switch (role) {
    case "ROLE_ADMIN":
      return "Admin";
    case "ROLE_SENIOR":
      return "Sénior";
    case "ROLE_APPRENTI":
      return "Apprenti";
    // Ajoutez d'autres cas ici pour d'autres rôles si nécessaire
    default:
      return role; // Retourne le rôle inchangé par défaut
  }
}
