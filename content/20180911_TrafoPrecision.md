---
Title: Numeric Precision in Trafo
Description: Dr. Shirota's Programming Blog (1)
Author: Thomas Ortner
Date: 2018-11-09
Robots: noindex,nofollow
Template: post
---
# Numeric Precision in Trafo

We want to render a number of points, therefore we create a 3D pointsprite for each one. Since the graphicscard prefers single precision floating point numbers we have to map `V3d` to `V3f`, throwing away half of our precision.

```javascript
let drawColoredPoints (points : alist<V3d>) =
  let pointsF = 
    points 
      |> AList.map V3f
      |> AList.toMod 
      |> Mod.map PList.toArray

  Sg.draw IndexedGeometryMode.PointList
    |> Sg.vertexAttribute DefaultSemantic.Positions pointsF
    |> Sg.effect [
       toEffect DefaultSurfaces.trafo
       toEffect (DefaultSurfaces.constantColor C4f.Red)
       Shader.PointSprite.Effect
    ]
    |> Sg.uniform "PointSize" (Mod.constant 10.0)
```

This becomes visually apparent as jittering artefacts and typically happens, when we deal with large coordinates before the `.`, e.g. 100k kilometers, but still need centimeter scale after the `.`. To remedy this we can use `DefaultSurfaces.stableTrafo`, which looks at the *model* *view* *projection* matrix as individual matrices and allows us to perform high precision transformations. This only works if we provide a *model* matrix, which then lets the *model* matrix and the *view* matrix "cancel " each other out in terms of large coordinates. Consequently, we need to transform `points` close to the origin and give the shader a `model` trafo, looking like this:

```javascript
let drawColoredPoints (points : alist<V3d>) =
  
  let head =
    points
      |> AList.toMod 
      |> Mod.map(fun x -> (PList.tryAt 0 x) |> Option.defaultValue V3d.Zero)

  let pointsF = 
    points 
      |> AList.toMod 
      |> Mod.map2(
        fun h points -> 
          points |> PList.map(fun (x:V3d) -> (x-h) |> toV3f) |> PList.toArray
          ) head

  Sg.draw IndexedGeometryMode.PointList
    |> Sg.vertexAttribute DefaultSemantic.Positions pointsF
    |> Sg.effect [
       toEffect DefaultSurfaces.stableTrafo
       toEffect (DefaultSurfaces.constantColor C4f.Red)
       Shader.PointSprite.Effect
    ]
    |> Sg.translate' head
    |> Sg.uniform "PointSize" (Mod.constant 10.0)

```

First, we take the head of our points, in probably not the most convenient way (*subject to change*), and use it as an offset to transform our points close to the origin. Of course, floating point numbers have the highest precision around the origin, since only little memory is wasted on large coordinates. We build a buffer with the shifted points and reapply our shift via `Sg.translate'` to show them at the right position.