<?php
// Process Info


/* Pookies Notes
11:53 PookiesRevenge: The model should handle all the data binding and database stuff.
11:53 PookiesRevenge: The controller is meant to process the data and prepare it for the view.
11:53 PookiesRevenge: Then the view renders it (obviously)
11:55 PookiesRevenge: Your model should have corresponding data-bindings for them, and then they should be passed to the controller (ideally).
11:56 PookiesRevenge: ie, if you have a "Fruit" variable in the view, the view would ask the controller wtf to put in there. The controller would either have a static value, or it would seek a data-bound value from the model. The model would then look that value up in the database and pass it to the controller in response to it's request.
11:58 PookiesRevenge: The db open/close would be in your model for sure. I don't have a 100% grasp on this particular project, but ideally your model should have data-bindings for members/posts/comments and fetch those from the db when requested.
*/
