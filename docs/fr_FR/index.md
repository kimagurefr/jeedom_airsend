Description 
===

Plugin permettant d’utiliser un (ou plusieurs) appareil AirSend avec Jeedom. Ce plugin se connecte en priorité en réseau local à l'appareil. Il ne nécessite donc pas de connexion internet pour fonctionner (sauf si votre AirSend n'est pas en réseau local).

Configuration du plugin
===

Après téléchargement du plugin, il faut l'activer puis cliquer sur "Attribution des droits aux scripts".

Configuration des équipements
===

La configuration des équipements est accessible à partir du menu plugins -> protocole domotique -> AirSend.

### Ajout de l'appareil AirSend

- Cliquez sur "Ajouter" et donnez un nom à cet appareil.
- Choisissez un "Objet parent", les sondes de l'appareil s'afficheront à cet endroit.
- Cliquez sur "Activer" et "Visible".
- Choisissez "Type d'appareil" : "Boiter AirSend".
- Renseignez le "LocalIP" et "Password" se trouvant au dos de votre appareil AirSend.
- Pour finir cliquez sur "Sauver/Générer".

A ce stade votre appareil est connecté et vous devriez voir la température et l'éclairement ambiant dans votre "Dashboard".

### Ajout des appareils sans fil

Pour ajouter des appareils sans fils il faut au préalable utiliser l'application mobile Android ou Iphone.

Ensuite il y a 3 possibilités pour récupérer ces appareils : 
#### - Ajout manuel de chaque télécommande avec le Protocole et l'Adresse

Vous retrouvez ces informations en modifiant chaque télécommande dans l'application mobile. Cependant cette méthode ne fonctionne pas correctement avec les télécommandes 1-bouton.

#### - Import d'un fichier

Depuis l'application mobile, accédez au menu "Paramètres" (en haut à droite), puis choisissez "Exporter". Le plus simple est de vous l'envoyer par mail, puis télécharger ce fichier et l'importer dans Jeedom.

#### - Import mobile.

L'import mobile est de loin la solution la plus simple.
- Dans Jeedom cliquez sur "Import mobile"
- Connectez votre mobile sur le réseau wifi en liaison avec votre Jeedom.
- Depuis l'application mobile, accédez au menu "Paramètres" (en haut à droite).
- Cliquez sur "Easy Scan", cela ouvre une application externe de scan de QRCode.
- Scannez le QRCode affiché dans Jeedom
- Acceptez l'avertissement de sécurité

Vos appareils sont maintenant dans Jeedom, il vous reste à les "activer" et choisir votre "objet parent" pour le placement dans le "Dashboard".

Liste des appareils sans fil compatibles
===

Vous trouverez la liste des appareils sans fil compatibles : <https://devmel.com/fr/airsend.html#compatibility>


