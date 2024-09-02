<?php
/**
 * @author Decreton Jérémy
 *
*/
namespace App\Services;

use Symfony\Contracts\Translation\TranslatorInterface;

class MessageGenerator
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getMessage(): string
    {
        $messages = [
            'hp1_label',
            'hp2_label',
            'hp3_label',
            'hp4_label',
            'hp5_label',
            'hp6_label',
            'hp7_label',
            'hp8_label',
            'hp9_label',
            'hp10_label',
            'hp11_label',
            'hp12_label',
            "hp13_label",
            "hp14_label",
        ];
        $index = array_rand($messages);
        return $this->translator->trans($messages[$index]);
    }

    public function getHappyMessage(): string
    {
        $messages = [
            "Welcome to Clean This, where dirt meets its match!",
            "Step into Clean This - where cleanliness is our commitment!",
            "Welcome to Clean This, where we turn mess into marvelous!",
            "Hello and welcome to Clean This, your go-to for spotless spaces!",
            "Enter Clean This - where cleanliness reigns supreme!",
            "Welcome aboard Clean This, where we make cleaning a breeze!",
            "Step into Clean This, where cleanliness is next to none!",
            "Welcome to Clean This, your partner in pristine spaces!",
            "Hello from Clean This - where cleanliness is our calling card!",
            "Welcome to Clean This, where cleanliness begins and ends with us!"
        ];
        $index = array_rand($messages);

        return $messages[$index];
    }

    public function getJoyeuxMessage(): string
    {
        $messages = [
            "Préparez-vous à accueillir une maison étincelante ! Avec nous, c'est du propre !",
            "Dites adieu au stress du nettoyage, et bonjour à une maison impeccable !",
            "Avec Clean This, c'est du nettoyage sans stress... et avec un sourire en prime !",
            "Votre satisfaction est notre priorité numéro un. Préparez-vous à être émerveillé !",
            "Confiez-nous votre maison, et nous la transformerons en un lieu de pure détente et de confort.",
            "Clean This, c'est comme un spa... pour votre maison !",
            "Du sol au plafond, nous ne laissons aucune poussière derrière nous. Prêt à être épaté ?",
            "Avec Clean This, même la poussière prend ses jambes à son cou !",
            "Pour une maison qui brille de propreté, faites confiance à notre équipe dévouée !",
            "Avec Clean This, c'est comme un coup de baguette magique... pour votre maison !"
        ];
        $index = array_rand($messages);

        return $messages[$index];
    }
}
?>
