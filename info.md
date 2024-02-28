# **Concerne :** Note globale d'hospitalisation (tiv_diet2)

Ce rapport est demandé uniquement pour les mois de juin et septembre de chaque année.

## API
Retourne les différents champs d’une note. Elle passe via la BS **NoteDieteNutriAdulteToJSONFileIn** dans l'acceptance d'IRIS, le passage des notes se fait donc ici.

L'utilisation est la suivante :
1.	Je dépose un fichier txt qui contient des specnoteid séparés par des virgules (1,2,3) dans le dossier **\\vdfs04-dev\DATA\NoteExport\diete**;
2.	Le flux **NoteDieteNutriAdulteToJSONFileIn** dans iris acceptance récupère le fichier ;
3.	Appel de la méthode **findNoteDieteNutriAdulteInfos** dans la classe **Tivoli.UTILS.FunctionSet** (Studio) ;
4.	Cette méthode appelle, en web service GET à l’adresse : 
http://intranet-common.bureautique.local/chupmbws/ebdapptiv/v1/note-export/diete-nutrition-adulte/(le specnoteid ici)
5.	Cette adresse appelle la classe **chupmb.ebdapptiv.api.REST.interface.NoteExport** dans le namespace chupmb
6.	La classe appelle la méthode **noteExportDieteNutritionAdulte** qui fait un appel à la méthode **getAllInfosFromNote** de la classe **chupmb.ebdapptiv.api.service.NoteExport** qui se charge de récupérer toutes les infos de Care.

C’est ainsi que dans notre Iris, je vois des messages, en json, partir vers la destination mise dans le connecteur et qui contient les infos de la note (les balises xml).

### CONVERTION DATE
Dans la table des notes, les dates sont inscrites au format caché ce qui les rend illisible. Si je veux lire une date, je dois la convertir :
1.	Pour convertir les dates, il faut aller dans studio (dans n'importe quelle namespace de n'importe quel serveur) ;
2.	Ecrire la requete suivante dans la console :
write $zdatetimeh("03/11/2023 12:00:00")  et $zdatetime() pour retourner la valeur date classique (zdate si je veux pas récupérer l'heure)

## MISE EN PLACE
Actuellement le mise en place est intégralement réalisée en PHP. 

Le code est disponible ici : https://github.com/drizzor/tivoli-diet2Export . En attendant que je le mette sur le Git interne.

Peut-être utilisé en local avec XAMP, WAMP, etc. Dès lors que je suis sur l’application voici comment procéder : 

**1.  Envoyer un CSV avec le specnoteid**

Je dois faire la requête ci-dessous dans la DB de Care  : 
```
select S.id
from bdoc.SpecNote as S 
where S.site = 'TIV'
and S.reportDefinition->definition->name = 'tiv_diet2'
ORDER BY S.id ASC 
```
Je sauvegarde les ID dans un fichier CSV ceux-ci devront être visible dans une colonne : 
  
**2. Uploader mon fichier CSV**

Pour l’instant le système fonctionne par année. Chaque ID seront analysé et ceux ne correspondant pas à l’année indiquée seront rejeté. 

**3. Récupérer l’import**
Dans le bandeau de droite va apparaitre tous les fichiers qui auront été généré et qui sont téléchargeable. A noter que les patients test sont automatiquement filtré et donc non inclus.

**4. Envoi du CSV**

Pour l’instant le processus n’est pas automatisé, je dois envoyer un e-mail avec le CSV joint à la diet qui au moment voulu nous environt un e-mail pour récupérer les informations

La diet copient eux-mêmes les données dans l’Excel fourni par le SPF.

<span style="color: #df4655">
Attention : En cas de modification de la note Note globale d'hospitalisation (tiv_diet2). Les champs utilisé et extrait ne peuvent pas être modifié, pareil si le nom de la note change, nécessite une modification dans le code. 
</span>
