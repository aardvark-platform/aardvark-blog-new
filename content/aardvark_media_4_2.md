---
Title: New aardvark.media is out
Description: Bunch of great new features in aardvark.media
Author: Aardvark Platform Team
Date: 2018-08-21
Robots: noindex,nofollow
Template: post
---
# New features of aardvark.media 4.2.*

Next version is a big one... `Showing 106 files with 10,950 additions and 3,366 deletions.` [diff](https://github.com/aardvark-platform/aardvark.media/commit/6caf11966e0e24d51cfd043d5ea0b8b862281ddc)

# Mapped direct mode for render controls

So called `mapped` render controls significantly improve performance for clients running on localhost (works via krauthaufens [node plugin](https://www.npmjs.com/package/node-shared-mem)  [(github)](https://github.com/aardvark-platform/node-shared-mem) using memory mapped files for
inter process communication and heavily krauthaufen tuned image download mechanisms).

aardium doesn ot hook global events
labelledinputs
screenshots

# Smooth new camera controller

We introduced two new events: `preRender` and `onRendered` for render controls. Pre render is invoked immediately before rendering takes place
and can be used as point for performing interpolation on animated data. `onRendered` on the other hand can be used to trigger animations instead
of utilizing the `ThreadPool`. 
In this course we implemented a new camera controller which runs much smoother, mainly due to correct interpolation for user inputs (no sampled jitter of inputs vs
rendering frequency). Thus: 
 - the old camera controller is obsolete (the module is marked as such)
 - we implemented a smooth free fly camera controller residing in `Aardvark.UI.Primitives.FreeFlyController`. Note that it returns `ThreadPool.empty` instead of timed stuff
 for animations.
 - a new [example](https://github.com/aardvark-platform/aardvark.media/blob/master/src/Examples%20(dotnetcore)/18%20-%20CameraControllerSettings/App.fs) shows the parameters. 

 ![Image of Yaktocat](%base_url%/assets/newCameraController.png)
 
 The code is really beautiful and is an excellent example of composable functional programming, here in the domain of signal processing.

 We use a differentiated type: `type CameraMotion = { dPos : V3d; dRot : V3d; dMoveSpeed : float; dZoom : float; dPan : V2d; dDolly : float }` which provides functions for multiplying scalars, a zero element and a function for applying the delta to the state (seealso)[https://github.com/aardvark-platform/aardvark.media/blob/master/src/Aardvark.UI.Primitives/FreeFlyController.fs#L19].

The real integration (with support for long frames looks as such):
``` 
let look (state : CameraControllerState) =
    if state.targetPhiTheta <> V2d.Zero then
                    
        let rr = (state.freeFlyConfig.lookAtConstant + abs state.targetPhiTheta.Y * state.freeFlyConfig.lookAtDamping) * float (sign (state.targetPhiTheta.Y))
        let ru = (state.freeFlyConfig.lookAtConstant + abs state.targetPhiTheta.X * state.freeFlyConfig.lookAtDamping) * float (sign (state.targetPhiTheta.X))

        { CameraMotion.Zero with
             dRot = V3d(rr, ru, 0.0)
        }
    else
        CameraMotion.Zero
let step = Integrator.rungeKutta (fun t s -> move s + look s + pan s + dolly s + zoom s)
Integrator.integrate 0.0166666 step model dt
```

and krauthaufens beautiful integration module:
```
module Integrator = 

    let inline private dbl (one) = one + one    
    let inline rungeKutta (f : ^t -> ^a -> ^da) (y0 : ^a) (h : ^t) : ^a =
        let twa : ^t = dbl LanguagePrimitives.GenericOne
        let half : ^t = LanguagePrimitives.GenericOne / twa
        let hHalf = h * half    
        let k1 = h * f LanguagePrimitives.GenericZero y0
        let k2 = h * f hHalf (y0 + k1 * half)
        let k3 = h * f hHalf (y0 + k2 * half)
        let k4 = h * f h (y0 + k3)
        let sixth = LanguagePrimitives.GenericOne / (dbl twa + twa)
        y0 + (k1 + twa*k2 + twa*k3 + k4) * sixth    
    let inline euler (f : ^t -> ^a -> ^da) (y0 : ^a) (h : ^t) : ^a=
        y0 + h * f LanguagePrimitives.GenericZero y0    
    let rec integrate (maxDt : float) (f : 'm -> float -> 'm) (m0 : 'm) (dt : float) =
        if dt <= maxDt then
            f m0 dt
        else
            integrate maxDt f (f m0 maxDt) (dt - maxDt) 
```

# Improved implementation of SubApps

An almost complete rewrite of the UI updater module allows us to use nested subapps with the full power of UI.map.

# Screenshots for free

`F12` now lets you download a screenshot of your focused render control.

# In the next aardium version

- aardium no longer globally hooks F10/F11/F5 which collides with visual studio shortcuts
- screenshot mechanism for aardium windows

# Breaking changes 

- `extractAttributes` lost a useless parameter: `CameraController.extractAttributes : MCameraModel -> (CameraController.Message -> 'msg) -> amap<string,AttributeValue<'msg>>`
- `CameraController` module is deprecated


# Upcoming

- mouseEvents get double values instead of integers in order to reflect (e.g. [clientX](https://developer.mozilla.org/en-US/docs/Web/API/MouseEvent/clientX))

# Minor stuff

- `MutableApp` now again provides `updateSync`
- `labelFloatInput` provides lightweight input mechanism for floating point stuff in your models. (also used in camera controller [app](https://github.com/aardvark-platform/aardvark.media/blob/master/src/Examples%20(dotnetcore)/18%20-%20CameraControllerSettings/App.fs#L72))