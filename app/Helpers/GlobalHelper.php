<?php

//* Get Publisher Info By Id
function getPublisherInfo($id)
{
    $publisher = \App\Models\Publishers::find($id);
    return $publisher ? $publisher : null;
}

//* Get Language Info
function getLanguageInfo($id)
{
    $langauge = \App\Models\Languages::find($id);
    return $langauge ? $langauge : null;
}

//* Get Material Type
function getMaterialTypeInfo($id)
{
    $materialTypeInfo = \App\Models\MaterialTypes::find($id);
    return $materialTypeInfo ? $materialTypeInfo : null;
}

//* Get Material Category Info
function getMaterialCategoryInfo($id)
{
    if ($id === 0) {
        return "Free";
    } else if ($id === 1) {
        return "Basic";
    } else if ($id === 2) {
        return "Premium";
    } else {
        return null;
    }
}


//* Get Genre Info
function geGenreInfo($id)
{
    $genre = \App\Models\Genres::find($id);
    return $genre ? $genre : null;
}


//* Get Subject Info
function geSubjectInfo($id)
{
    $subject = \App\Models\Subjects::find($id);
    return $subject ? $subject : null;
}


//* Plan Info

function getSubscribtionPlanInfo($id)
{
    $plan = \App\Models\SubscriptionPlans::find($id);
    return $plan ? $plan : null;
}


//* Age Group
function ageGroups()
{
    return [
        ['id' => 0, 'group' => "All Age Groups"],
        ['id' => 1, 'group' => "Foundational Stage (03-08 Years)"],
        ['id' => 2, 'group' => "Preparatory Stage (08-11 Years)"],
        ['id' => 3, 'group' => "Middle Stage (11-14 Years)"],
        ['id' => 4, 'group' => "Secondary Stage (14 to 18 Years)"],
        ['id' => 5, 'group' => "Higher Education (18 to 22 Years)"]
    ];
}
