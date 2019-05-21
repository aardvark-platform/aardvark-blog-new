---
Title: Action lifting
Description: Code review by example
Author: Thomas Ortner
Date: 2019-03-01
Robots: noindex,nofollow
Template: post
---
# Action lifting

We have a higher app that needs to pass on input parameters (mousemove, mousedown, keys, ...) to a lower app, because only the higher app can actually create these input actions because it owns the window.

## Old

```fsharp
//udpate multiple matches
match msg, model.drawingApp.isDrawing with
  | MouseUp bp,_ ->
    let message =
      (CorrelationPlotApp.Action.CorrelationPlotMessage
        (CorrelationPlot.Action.SvgCameraMessage 
          (SvgCamera.Action.MouseUp bp)
        )
      )
    {model with 
      corrPlot =
       CorrelationPlotApp.update model.annotationApp 
                                 model.corrPlot 
                                 message
    }
  | MouseDown bp,_ -> ...

//view
require (GUI.CSS.myCss) (
  body [attribute "overflow-x" "hidden";
        attribute "overflow-y" "hidden"; 
        (onMouseDown (fun b p -> MouseDown (b,p)))
        (onMouseUp (fun b p -> MouseUp (b,p)))
        (onMouseMove (fun p -> MouseMove (V2d p)))
        onLayoutChanged UpdateConfig
       ] [
        CorrelationPlotApp.viewSvg model.annotationApp.annotations model.corrPlot
          |> (UI.map CorrPlotMessage)
  ]
)
```

The above approach subscribes the lower actions directly, which then leads to a tedious matching and passing on of these input values in the higher app to the lower app. However, higher apps can always send actions from lower apps neatly by wrapping them in the respective composing actions.

## New

```fsharp
//update
match msg with
  | CorrPlotMessage a -> 
    let cp = CorrelationPlotApp.update' annotations' cp a
    { m with cpModel = cp }

//view
let viewSvg (m : MCorrelationPanelsAppModel) =
  
  let lift =
    CorrelationPlot.Action.SvgCameraMessage >> 
    CorrelationPlotApp.Action.CorrelationPlotMessage >> 
    CorrelationPanelsMessage.CorrPlotMessage

  require (GUI.CSS.myCss) (
    body [attribute "overflow-x" "hidden";
          attribute "overflow-y" "hidden"; 
          (onMouseDown (fun b p -> SvgCamera.Action.MouseDown (b,p)   |> lift))
          (onMouseUp   (fun b p -> SvgCamera.Action.MouseUp (b,p)     |> lift))
          (onMouseMove (fun p   -> SvgCamera.Action.MouseMove (V2d p) |> lift))
          //onLayoutChanged UpdateConfig
         ] [
          CorrelationPlotApp.viewSvg m.annotations m.cpModel |> (UI.map CorrPlotMessage)
    ]
  )
```

This updated approach uses a lifter via function composition to elevate the lower actions to the level of the composing higher app, which is `CorrPlotMessage`. This allows us to match all `SvgCameraMessage`s in their composed form and pass them on to the lower app. We do not have keyboard interactions at this point, but in general it is advised to only use MouseDown,MouseUp,MouseMove, KeyUp, and KeyDown in the highest app and translate these raw input messages into sth. more meaningfull which can then be passed on. For instance, `MouseMove` to `PanView` in the case of the `SvgCamera`. This also helps users, who want to reuse the `SvgCamera`, to understand which actions it supports and how to trigger these from outside. This concept comes from the Domain Driven Design approach, which is also very useful in our own domain of creating visualizations.